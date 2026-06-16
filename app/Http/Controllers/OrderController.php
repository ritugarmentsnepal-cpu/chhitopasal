<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\PathaoService;
use App\Services\OrderService;
use App\SystemAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'failed', 'rejected', 'return_delivered'];
        
        if (!in_array($status, $validStatuses)) {
            $status = 'pending';
        }

        $search = $request->get('search');
        $dateFilter = $request->get('date_filter');

        $query = Order::with('orderItems.product')->where('status', $status);

        if ($search) {
            $escaped = OrderService::escapeLike($search);
            $query->where(function($q) use ($escaped) {
                $q->where('id', 'like', "%{$escaped}%")
                  ->orWhere('customer_name', 'like', "%{$escaped}%")
                  ->orWhere('customer_phone', 'like', "%{$escaped}%");
            });
        }

        if ($dateFilter) {
            if ($dateFilter === 'today') {
                $query->whereDate('created_at', \Carbon\Carbon::today());
            } elseif ($dateFilter === 'yesterday') {
                $query->whereDate('created_at', \Carbon\Carbon::yesterday());
            } elseif ($dateFilter === 'this_week') {
                $query->whereBetween('created_at', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($dateFilter === 'this_month') {
                $query->whereMonth('created_at', \Carbon\Carbon::now()->month)
                      ->whereYear('created_at', \Carbon\Carbon::now()->year);
            }
        }

        // Shipped date filter (filters by shipped_at timestamp for shipped and post-shipped tabs)
        $shippedDateFilter = $request->get('shipped_date_filter');
        if ($shippedDateFilter && in_array($status, ['shipped', 'delivered', 'return_delivered', 'failed', 'rejected'])) {
            if ($shippedDateFilter === 'today') {
                $query->whereDate('shipped_at', \Carbon\Carbon::today());
            } elseif ($shippedDateFilter === 'yesterday') {
                $query->whereDate('shipped_at', \Carbon\Carbon::yesterday());
            } elseif ($shippedDateFilter === 'this_week') {
                $query->whereBetween('shipped_at', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]);
            } elseif ($shippedDateFilter === 'this_month') {
                $query->whereMonth('shipped_at', \Carbon\Carbon::now()->month)
                      ->whereYear('shipped_at', \Carbon\Carbon::now()->year);
            }
        }

        // Pathao delivery status filter (for shipped and post-shipped tabs)
        $pathaoFilter = $request->get('pathao_filter');
        if ($pathaoFilter && in_array($status, ['shipped', 'delivered', 'return_delivered', 'failed', 'rejected'])) {
            if ($pathaoFilter === 'awaiting_pickup') {
                $query->where(function($q) {
                    $q->whereNull('pathao_status')->orWhere('pathao_status', '');
                });
            } else {
                $escapedFilter = OrderService::escapeLike($pathaoFilter);
                $query->where('pathao_status', 'like', "%{$escapedFilter}%");
            }
        }

        $perPage = max(1, min(500, (int) $request->get('per_page', 20))) ?: 20;
        $orders = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

        // Fetch products for bulk upload reference modal
        $products = Product::select('id', 'name', 'price', 'stock', 'bundles')->get();
        $accounts = \App\Models\Account::all();

        return view('orders.index', compact('orders', 'status', 'products', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
            'source' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::findOrFail($validated['product_id']);
            $quantity = $validated['quantity'];
            $totalAmount = $product->price * $quantity;
            $unitPrice = $product->price;

            // ORD-05: Apply bundle pricing in manual orders
            if (!empty($product->bundles) && is_array($product->bundles)) {
                $matchedBundle = collect($product->bundles)->first(function($bundle) use ($quantity) {
                    return (int)$bundle['qty'] === (int)$quantity;
                });
                if ($matchedBundle && isset($matchedBundle['price'])) {
                    $totalAmount = (float)$matchedBundle['price'];
                    $unitPrice = $totalAmount / $quantity;
                }
            }

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'address' => $validated['address'],
                'city' => $validated['city'] ?? null,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'source' => $validated['source'] ?? 'manual',
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $order->orderItems()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price_at_purchase' => $unitPrice,
                'cost_at_purchase' => $product->cost_price,
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Order created successfully from Quick Order!'
            ]);
        }

        return redirect()->route('orders.index', ['status' => 'pending'])->with('success', 'Order created manually.');
    }

    public function storePOS(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_charge' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,credit',
            'paid_amount' => 'required|numeric|min:0',
            'party_id' => 'nullable|exists:parties,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $totalAmount = 0;
            $orderItemsData = [];
            
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $price = isset($item['price']) ? (float)$item['price'] : $product->price;
                $totalAmount += $price * $item['quantity'];
                
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $price,
                    'cost_at_purchase' => $product->cost_price,
                ];
            }

            $deliveryCharge = isset($validated['delivery_charge']) ? (float)$validated['delivery_charge'] : 0;
            $totalAmount += $deliveryCharge;

            $paidAmount = (float)$validated['paid_amount'];
            if ($paidAmount > $totalAmount) {
                $paidAmount = $totalAmount;
            }

            $paymentStatus = 'unpaid';
            if ($paidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }

            // SEC-HIGH-07: Log POS sale with user and IP for audit trail
            Log::info('POS Sale initiated', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'ip' => request()->ip(),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'payment_method' => $validated['payment_method'],
                'delivery_charge' => $deliveryCharge,
                'item_count' => count($validated['items']),
            ]);

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'address' => 'Store Pickup',
                'city' => 'Local',
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $totalAmount,
                'status' => 'delivered',
                'payment_status' => $paymentStatus,
                'paid_amount' => $paidAmount,
                'source' => 'pos',
            ]);

            foreach ($orderItemsData as $data) {
                $order->orderItems()->create($data);
            }

            // FIN-01: Use OrderService for consistent stock deduction with failure logging
            $orderService = app(OrderService::class);
            $orderService->deductStock($order);

            // Record unpaid balance to party ledger if applicable
            $unpaidAmount = $totalAmount - $paidAmount;
            if ($validated['payment_method'] === 'credit' && !empty($validated['party_id']) && $unpaidAmount > 0) {
                $party = \App\Models\Party::find($validated['party_id']);
                if ($party) {
                    $party->increment('current_balance', $unpaidAmount);
                    
                    \App\Models\Transaction::create([
                        'party_id' => $party->id,
                        'type' => 'in',
                        'amount' => $unpaidAmount,
                        'reference_type' => SystemAccounts::REF_ORDER,
                        'reference_id' => $order->id,
                        'date' => now(),
                        'notes' => 'Credit Sale (Unpaid Balance)'
                    ]);
                }
            }

            // Record cash revenue
            $cashAccount = SystemAccounts::mainCash();
            if (!$cashAccount) {
                Log::error('POS Sale: Main Cash account not found. Financial transaction skipped for Order #' . $order->id);
            } elseif ($paidAmount > 0) {
                \App\Models\Transaction::create([
                    'account_id' => $cashAccount->id,
                    'type' => 'in',
                    'amount' => $paidAmount,
                    'reference_type' => SystemAccounts::REF_ORDER,
                    'reference_id' => $order->id,
                    'date' => now(),
                    'notes' => 'POS Sale Payment (' . ucfirst($validated['payment_method']) . ')'
                ]);
                $cashAccount->increment('balance', $paidAmount);
            }

            return $order;
        });

        return redirect()->route('orders.invoice', $order);
    }

    public function invoice(Order $order)
    {
        // SEC-MED-07: Only authenticated users can view invoices
        if (!auth()->check()) {
            abort(403, 'Unauthorized access.');
        }
        return view('orders.invoice', compact('order'));
    }

    public function storeWeb(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'string', 'max:20', 'regex:/^(\+?977)?[9][6-8]\d{8}$/'],
            'address' => 'required|string|max:255',
            'delivery_location' => 'nullable|string|in:inside,outside',
            'items' => 'required|array|min:1|max:20',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:10', // SEC-HIGH-06: Max 10 per item
            'items.*.color' => 'nullable|string|max:50',
            'items.*.size' => 'nullable|string|max:50',
        ]);

        // ORD-04: Validate stock availability before creating order
        $orderService = app(OrderService::class);
        $stockCheck = $orderService->validateStockAvailability(
            array_map(fn($i) => ['id' => $i['id'], 'quantity' => $i['quantity']], $validated['items'])
        );
        if (!$stockCheck['valid']) {
            return response()->json(['message' => implode(' ', $stockCheck['errors'])], 422);
        }

        // Server-side delivery charge calculation — never trust client amounts
        $insideValleyCharge = (float) setting('delivery_charge_inside', 50);
        $outsideValleyCharge = (float) setting('delivery_charge_outside', 100);
        $deliveryLocation = $validated['delivery_location'] ?? 'inside';
        $deliveryCharge = $deliveryLocation === 'outside' ? $outsideValleyCharge : $insideValleyCharge;

        $order = DB::transaction(function () use ($validated, $deliveryCharge) {
            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['id']);
                if (!$product) continue;
                $quantity = $item['quantity'];
                
                $itemTotal = $product->price * $quantity;
                $unitPrice = $product->price;

                if (!empty($product->bundles) && is_array($product->bundles)) {
                    $matchedBundle = collect($product->bundles)->first(function($bundle) use ($quantity) {
                        return (int)$bundle['qty'] === (int)$quantity;
                    });

                    if ($matchedBundle && isset($matchedBundle['price'])) {
                        $itemTotal = (float)$matchedBundle['price'];
                        $unitPrice = $itemTotal / $quantity;
                    }
                }

                $totalAmount += $itemTotal;
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price_at_purchase' => $unitPrice,
                    'cost_at_purchase' => $product->cost_price,
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                ];
            }

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'address' => $validated['address'], 
                'city' => null,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $totalAmount + $deliveryCharge,
                'status' => 'pending',
                'source' => 'web',
                'session_id' => request()->cookie('visitor_session_id'),
            ]);

            $order->orderItems()->createMany($orderItemsData);
            return $order;
        });

        return response()->json(['message' => 'Order created successfully!', 'order_id' => $order->id], 201);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Sheet 1: Main Data Entry
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Orders');
        
        // Headers
        $headers = ['Customer Name', 'Customer Phone', 'Address', 'City', 'Product Selection'];
        foreach ($headers as $index => $header) {
            $col = chr(65 + $index);
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Sheet 2: Hidden Dropdown Data
        $dropdownSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'DropdownData');
        $spreadsheet->addSheet($dropdownSheet);
        $dropdownSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        $products = Product::where('stock', '>', 0)->get();
        $options = [];
        foreach ($products as $product) {
            // Main Product
            $options[] = "[{$product->id}:1] {$product->name}";
            // Bundles
            if (!empty($product->bundles) && is_array($product->bundles)) {
                foreach ($product->bundles as $bundle) {
                    $qty = $bundle['qty'];
                    $options[] = "[{$product->id}:{$qty}] {$product->name} ({$qty}-Pack)";
                }
            }
        }

        // Write options to hidden sheet
        foreach ($options as $index => $option) {
            $dropdownSheet->setCellValue('A' . ($index + 1), $option);
        }

        // Create Data Validation for Product Column (Column E, rows 2 to 1000)
        $validation = $sheet->getCell('E2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Please select a valid product from the dropdown.');
        $validation->setPromptTitle('Select Product');
        $validation->setPrompt('Please choose a product or bundle.');
        
        // Use formula to reference the hidden sheet column A
        $totalOptions = count($options);
        if ($totalOptions > 0) {
            $validation->setFormula1('DropdownData!$A$1:$A$' . $totalOptions);
        }

        // Apply validation to rows 2 to 1000
        for ($i = 2; $i <= 1000; $i++) {
            $sheet->getCell("E{$i}")->setDataValidation(clone $validation);
        }

        // Export
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'order_import_template.xlsx';
        $tempPath = storage_path('app/temp_' . uniqid() . '.xlsx');
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ]);

        $path = $request->file('excel_file')->getRealPath();
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $header = array_shift($rows); // Remove header
        
        $successCount = 0;
        $batchId = (string) \Illuminate\Support\Str::uuid();

        // NEW-FIN-04 + NEW-ARCH-01: Wrap in transaction, suppress logging for bulk
        Order::$suppressLogging = true;
        try {
            DB::transaction(function () use ($rows, $batchId, &$successCount) {
                foreach ($rows as $row) {
                    if (empty($row[0]) || empty($row[4])) continue;
                    
                    $customerName = trim($row[0]);
                    $phone = trim($row[1] ?? '');
                    $address = trim($row[2] ?? '');
                    $city = trim($row[3] ?? '');
                    $productSelection = trim($row[4]);
                    
                    if (preg_match('/\[(\d+):(\d+)\]/', $productSelection, $matches)) {
                        $productId = (int)$matches[1];
                        $qty = (int)$matches[2];
                    } else {
                        continue;
                    }
                    
                    $product = Product::find($productId);
                    if (!$product) continue;
                    
                    $itemTotal = $product->price * $qty;
                    $unitPrice = $product->price;

                    if (!empty($product->bundles) && is_array($product->bundles)) {
                        $matchedBundle = collect($product->bundles)->first(function($bundle) use ($qty) {
                            return (int)$bundle['qty'] === (int)$qty;
                        });
                        if ($matchedBundle && isset($matchedBundle['price'])) {
                            $itemTotal = (float)$matchedBundle['price'];
                            $unitPrice = $itemTotal / $qty;
                        }
                    }
                    
                    $order = Order::create([
                        'customer_name' => $customerName,
                        'customer_phone' => $phone,
                        'address' => $address,
                        'city' => $city,
                        'total_amount' => $itemTotal,
                        'status' => 'pending',
                        'source' => 'csv',
                        'bulk_batch_id' => $batchId,
                    ]);
                    
                    $order->orderItems()->create([
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'price_at_purchase' => $unitPrice,
                        'cost_at_purchase' => $product->cost_price,
                    ]);
                    $successCount++;
                }
            });
        } finally {
            Order::$suppressLogging = false;
        }

        return redirect()->back()->with('success', "Bulk uploaded $successCount orders.");
    }

    public function bulkManualStore(Request $request)
    {
        $orders = $request->input('orders', []);

        if (!is_array($orders) || count($orders) === 0) {
            return response()->json(['message' => 'No orders provided.'], 422);
        }

        if (count($orders) > 100) {
            return response()->json(['message' => 'Maximum 100 orders per batch.'], 422);
        }

        // All-or-nothing: validate every row, collect per-row errors
        $rowErrors = [];
        $hasEmptyRows = false;

        foreach ($orders as $index => $row) {
            // Check for completely empty rows — user must explicitly delete them
            $isEmpty = empty(trim($row['customer_name'] ?? ''))
                    && empty(trim($row['customer_phone'] ?? ''))
                    && empty(trim($row['address'] ?? ''))
                    && empty($row['product_id']);

            if ($isEmpty) {
                $hasEmptyRows = true;
                $rowErrors[$index] = ['Empty row — please delete this row or fill in the required fields.'];
                continue;
            }

            $rowValidator = Validator::make($row, [
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'city' => 'nullable|string|max:100',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'amount' => 'required|numeric|min:0',
                'remarks' => 'nullable|string|max:1000',
            ], [
                'customer_name.required' => 'Customer name is required.',
                'customer_phone.required' => 'Phone number is required.',
                'address.required' => 'Address is required.',
                'product_id.required' => 'Please select a product.',
                'product_id.exists' => 'Selected product does not exist.',
                'quantity.required' => 'Quantity is required.',
                'quantity.min' => 'Quantity must be at least 1.',
                'amount.required' => 'Amount is required.',
                'amount.min' => 'Amount cannot be negative.',
            ]);

            if ($rowValidator->fails()) {
                $rowErrors[$index] = $rowValidator->errors()->all();
            }
        }

        // If any errors, return them all — create nothing
        if (!empty($rowErrors)) {
            $errorCount = count($rowErrors);
            return response()->json([
                'message' => "{$errorCount} order(s) have errors. Please fix them and try again.",
                'row_errors' => $rowErrors,
            ], 422);
        }

        // All valid — create all orders in a single transaction with batch ID
        $batchId = (string) Str::uuid();
        $successCount = 0;

        Order::$suppressLogging = true;
        try {
            DB::transaction(function () use ($orders, $batchId, &$successCount) {
                foreach ($orders as $row) {
                    $product = Product::findOrFail($row['product_id']);

                    $totalAmount = $row['amount'];
                    $qty = $row['quantity'];
                    $unitPrice = $qty > 0 ? $totalAmount / $qty : 0;

                    $order = Order::create([
                        'customer_name' => $row['customer_name'],
                        'customer_phone' => $row['customer_phone'],
                        'address' => $row['address'],
                        'city' => $row['city'] ?? null,
                        'total_amount' => $totalAmount,
                        'status' => 'pending',
                        'source' => 'manual',
                        'bulk_batch_id' => $batchId,
                        'remarks' => $row['remarks'] ?? null,
                    ]);

                    $order->orderItems()->create([
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'price_at_purchase' => $unitPrice,
                        'cost_at_purchase' => $product->cost_price,
                    ]);

                    $successCount++;
                }
            });
        } finally {
            Order::$suppressLogging = false;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully created {$successCount} orders.",
            'count' => $successCount,
            'bulk_batch_id' => $batchId,
        ]);
    }

    public function bulkBatches(Request $request)
    {
        $batches = Order::whereNotNull('bulk_batch_id')
            ->select('bulk_batch_id')
            ->selectRaw('MIN(created_at) as created_at')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('GROUP_CONCAT(id ORDER BY id ASC) as order_ids')
            ->groupBy('bulk_batch_id')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('orders.bulk_batches', compact('batches'));
    }

    public function bulkBatchShow($batchId)
    {
        $batchOrders = Order::with('orderItems.product')
            ->where('bulk_batch_id', $batchId)
            ->orderBy('id', 'asc')
            ->get();

        if ($batchOrders->isEmpty()) {
            return redirect()->route('orders.bulkBatches')->with('error', 'Batch not found or empty.');
        }

        $batchDate = $batchOrders->first()->created_at;

        return view('orders.bulk_batch_show', compact('batchOrders', 'batchId', 'batchDate'));
    }

    public function bulkPrint(Request $request)
    {
        // BACK-01: Validate order_ids properly
        $decoded = json_decode($request->input('order_ids', '[]'), true);
        if (!is_array($decoded) || empty($decoded)) {
            return redirect()->back()->with('error', 'No orders selected.');
        }
        $ids = array_map('intval', array_filter($decoded, 'is_numeric'));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Invalid order selection.');
        }

        $orders = Order::with('orderItems.product')->whereIn('id', $ids)->get();
        $printMode = $request->input('print_mode', 'a4');

        if ($printMode === 'thermal') {
            return view('orders.bulk_print_thermal', compact('orders'));
        }

        return view('orders.bulk_print', compact('orders'));
    }

    public function bulkDelete(Request $request)
    {
        // SEC-HIGH-08: Only users with orders.delete permission can bulk delete
        if (!auth()->user()->hasPermission('orders.delete')) {
            return response()->json(['message' => 'Access Denied: You do not have permission to delete orders.'], 403);
        }

        $validated = $request->validate([
            'order_ids' => 'required|array|min:1|max:500',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        // Only allow deleting pending orders
        $orders = Order::whereIn('id', $validated['order_ids'])
                       ->where('status', 'pending')
                       ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No pending orders found to delete.'], 422);
        }

        // NEW-ARCH-01: Suppress logging for bulk delete
        Order::$suppressLogging = true;
        $deletedCount = 0;
        try {
            foreach ($orders as $order) {
                $order->orderItems()->delete();
                $order->delete();
                $deletedCount++;
            }
        } finally {
            Order::$suppressLogging = false;
        }

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deletedCount} pending orders.",
            'count' => $deletedCount
        ]);
    }

    public function bulkShipments(Request $request)
    {
        $batches = Order::whereNotNull('bulk_ship_batch_id')
            ->select('bulk_ship_batch_id')
            ->selectRaw('MIN(COALESCE(shipped_at, created_at)) as shipped_at')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->groupBy('bulk_ship_batch_id')
            ->orderByDesc('shipped_at')
            ->paginate(20);

        return view('orders.bulk_shipments', compact('batches'));
    }

    public function bulkShipmentPrint(Request $request, $batchId)
    {
        $orders = Order::with('orderItems.product')->where('bulk_ship_batch_id', $batchId)->get();
        
        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'No orders found for this shipment lot.');
        }

        $printMode = $request->input('print_mode', 'a4');

        if ($printMode === 'thermal') {
            return view('orders.bulk_print_thermal', compact('orders'));
        }

        return view('orders.bulk_print', compact('orders'));
    }

    public function bulkShip(Request $request, PathaoService $pathao)
    {
        // Increase time limit for processing many orders
        set_time_limit(300);

        $validated = $request->validate([
            'order_ids' => 'required|array|min:1|max:500',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        $orders = Order::with('orderItems.product')
                       ->whereIn('id', $validated['order_ids'])
                       ->where('status', 'confirmed')
                       ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No confirmed orders found to ship.'], 422);
        }

        $shipped = 0;
        $failed = 0;
        $errors = [];
        $orderService = app(OrderService::class);
        $batchId = (string) Str::uuid();

        // NEW-ARCH-01: Suppress logging for bulk ship
        Order::$suppressLogging = true;
        Transaction::$suppressLogging = true;
        Product::$suppressLogging = true;
        try {
            foreach ($orders as $order) {
                if (!$order->pathao_city_id || !$order->pathao_zone_id) {
                    $failed++;
                    $errors[] = "Order #{$order->id}: Missing Pathao location IDs.";
                    continue;
                }

                try {
                    $result = $pathao->createOrder($order);

                    // Rate limit: 500ms delay after each Pathao API call
                    usleep(500000);

                    if ($result['success']) {
                        // NEW-FIN-03: Use OrderService for atomic stock deduction
                        DB::transaction(function () use ($order, $result, $orderService, $batchId) {
                            $order->update([
                                'pathao_consignment_id' => $result['consignment_id'],
                                'bulk_ship_batch_id' => $batchId
                            ]);
                            $orderService->transitionStatus($order, 'shipped');
                        });
                        $shipped++;
                    } else {
                        $failed++;
                        $errors[] = "Order #{$order->id}: " . ($result['error'] ?? 'Unknown error');
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Order #{$order->id}: " . $e->getMessage();
                }
            }
        } finally {
            Order::$suppressLogging = false;
            Transaction::$suppressLogging = false;
            Product::$suppressLogging = false;
        }

        $message = "Shipped: {$shipped}";
        if ($failed > 0) {
            $message .= ", Failed: {$failed}";
        }

        return response()->json([
            'success' => $shipped > 0,
            'message' => $message,
            'shipped' => $shipped,
            'failed' => $failed,
            'errors' => $errors
        ], $shipped > 0 ? 200 : 422);
    }

    public function bulkStatusUpdate(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1|max:500',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|string|in:confirmed,rejected,failed',
        ]);

        $newStatus = $validated['status'];
        $orders = Order::with('orderItems.product')
                       ->whereIn('id', $validated['order_ids'])
                       ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found.'], 422);
        }

        $updated = 0;
        $skipped = 0;
        $orderService = app(OrderService::class);

        // NEW-ARCH-01: Suppress logging for bulk status update
        Order::$suppressLogging = true;
        Transaction::$suppressLogging = true;
        try {
            foreach ($orders as $order) {
                if (!$orderService->isValidTransition($order->status, $newStatus)) {
                    $skipped++;
                    continue;
                }

                DB::transaction(function () use ($order, $newStatus, $orderService) {
                    $orderService->transitionStatus($order, $newStatus);
                });
                $updated++;
            }
        } finally {
            Order::$suppressLogging = false;
            Transaction::$suppressLogging = false;
        }

        $message = "Updated {$updated} orders to {$newStatus}.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} orders (invalid transition).";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => $updated
        ]);
    }



    public function shipWithPathao(Request $request, Order $order, PathaoService $pathao)
    {
        if ($order->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Only confirmed orders can be shipped.');
        }

        if (!$order->pathao_city_id || !$order->pathao_zone_id) {
            return redirect()->back()->with('error', 'Pathao location IDs are missing. Please edit the order to set the location.');
        }

        $result = $pathao->createOrder($order);

        if ($result['success']) {
            // NEW-FIN-03: Use OrderService for atomic stock deduction
            DB::transaction(function () use ($order, $result) {
                $order->update(['pathao_consignment_id' => $result['consignment_id']]);
                app(OrderService::class)->transitionStatus($order, 'shipped');
            });

            $printType = $request->input('print_type', 'both');
            return redirect()->route('orders.printLabel', ['order' => $order->id, 'type' => $printType])->with('success', 'Order shipped via Pathao! Consignment ID: ' . $result['consignment_id']);
        }

        return redirect()->back()->with('error', 'Pathao Error: ' . ($result['error'] ?? 'Unknown error'));
    }

    public function printLabel(Order $order)
    {
        return view('orders.print_label', compact('order'));
    }
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,confirmed,shipped,delivered,failed,rejected,return_delivered'
        ]);

        $newStatus = $validated['status'];
        $orderService = app(OrderService::class);

        // BUG-05: Catch invalid transition errors gracefully
        try {
            DB::transaction(function () use ($order, $newStatus, $orderService) {
                $orderService->transitionStatus($order, $newStatus);
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Order status updated to ' . ucfirst($newStatus));
    }

    public function syncPathaoStatus(Order $order, PathaoService $pathao)
    {
        if (!$order->pathao_consignment_id) {
            return redirect()->back()->with('error', 'Order has no Pathao consignment ID.');
        }

        // Force-refresh: user explicitly requested a sync, bypass the 2-min cache
        $status = $pathao->getOrderStatus($order->pathao_consignment_id, true);

        if (!$status) {
            return redirect()->back()->with('error', 'Could not fetch status from Pathao.');
        }

        $normalizedStatus = strtolower($status);
        $newLocalStatus = $order->status;

        // Always save the raw Pathao status for badge display
        $order->update([
            'pathao_status' => $status,
            'pathao_status_updated_at' => now(),
        ]);

        if (in_array($normalizedStatus, ['delivered', 'successful'])) {
            $newLocalStatus = 'delivered';
        } elseif (in_array($normalizedStatus, ['returned', 'return'])) {
            $newLocalStatus = 'return_delivered';
        } elseif (in_array($normalizedStatus, ['cancelled', 'cancel', 'pickup cancel', 'pickup cancelled'])) {
            $newLocalStatus = 'rejected';
        }

        if ($newLocalStatus !== $order->status) {
            $orderService = app(OrderService::class);
            try {
                DB::transaction(function () use ($order, $newLocalStatus, $orderService) {
                    $orderService->transitionStatus($order, $newLocalStatus);
                });
            } catch (\InvalidArgumentException $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
            return redirect()->back()->with('success', 'Pathao status synced successfully. Order is now ' . $newLocalStatus);
        }

        return redirect()->back()->with('success', 'Pathao status is still ' . $status);
    }

    public function masterSyncPathao()
    {
        set_time_limit(600); // 10 minutes max for force sync

        // MED-05: Cooldown lock to prevent Pathao API flooding
        $lockKey = 'pathao_master_sync_lock';
        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            return redirect()->back()->with('error', 'Please wait at least 5 minutes between sync operations.');
        }
        \Illuminate\Support\Facades\Cache::put($lockKey, true, now()->addMinutes(5));

        $exitCode = \Illuminate\Support\Facades\Artisan::call('pathao:sync', ['--force' => true]);
        
        if ($exitCode !== 0) {
            return redirect()->back()->with('error', \Illuminate\Support\Facades\Artisan::output());
        }
        
        return redirect()->back()->with('success', 'Master sync completed successfully. ' . \Illuminate\Support\Facades\Artisan::output());
    }

    public function updateAmount(Request $request, Order $order)
    {
        // AUTH-02: Only users with orders permission and delete sub-permission can modify amounts
        if (!auth()->user()->hasPermission('orders')) {
            return back()->with('error', 'Access Denied: You do not have permission to modify order amounts.');
        }

        // FIN-MED-01: Block amount changes on orders with recorded financial transactions
        if (in_array($order->status, ['delivered', 'return_delivered'])) {
            $hasFinancialTx = \App\Models\Transaction::where(function ($q) use ($order) {
                $q->where('reference_type', SystemAccounts::REF_ORDER_DELIVERED)
                  ->where('reference_id', $order->id);
            })->exists();

            if ($hasFinancialTx) {
                return back()->with('error', 'Cannot modify amount — financial transactions already recorded for this order. Use Sale Return instead.');
            }
        }

        $request->validate(['total_amount' => 'required|numeric|min:0']);
        
        $oldAmount = $order->total_amount;
        $order->update(['total_amount' => $request->total_amount]);

        // Explicitly log the activity
        $order->logActivity('updated_amount', [
            'old_amount' => $oldAmount,
            'new_amount' => $request->total_amount
        ]);

        return back()->with('success', 'Order amount updated successfully.');
    }

    public function fullUpdate(Request $request, Order $order)
    {
        // Admin can edit any order, others can only edit pending or confirmed
        if (!in_array($order->status, ['pending', 'confirmed']) && !auth()->user()->hasPermission('orders.delete')) {
            return back()->with('error', 'Only pending or confirmed orders can be edited by staff.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'pathao_city_id' => 'nullable|integer',
            'pathao_zone_id' => 'nullable|integer',
            'delivery_charge' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.total_price' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,confirmed,shipped,delivered,failed,rejected,return_delivered',
            'confirm_order' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // ORD-03: Wrap entire edit in a transaction for atomicity
        try {
        DB::transaction(function () use ($order, $validated) {

        $orderTotal = 0;

        $stockDeductedStatuses = ['shipped', 'delivered'];
        $isStockDeducted = in_array($order->status, $stockDeductedStatuses);

        $processedItemIds = [];

        foreach ($validated['items'] as $itemData) {
            $totalPrice = $itemData['total_price'];
            $newQty = $itemData['quantity'];
            $newProductId = $itemData['product_id'];
            $unitPrice = $newQty > 0 ? $totalPrice / $newQty : 0;
            $orderTotal += $totalPrice;

            if (!empty($itemData['id'])) {
                // Update existing item
                $orderItem = $order->orderItems()->find($itemData['id']);
                if ($orderItem) {
                    $processedItemIds[] = $orderItem->id;
                    $oldQty = $orderItem->quantity;
                    $oldProductId = $orderItem->product_id;

                    if ($isStockDeducted) {
                        if ($oldProductId != $newProductId) {
                            // Product changed: restore old, deduct new (atomic)
                            if ($orderItem->product) {
                                $orderItem->product->increment('stock', $oldQty);
                            }
                            $newProduct = Product::find($newProductId);
                            if ($newProduct) {
                                $affected = Product::where('id', $newProductId)
                                    ->where('stock', '>=', $newQty)
                                    ->decrement('stock', $newQty);
                                if ($affected === 0) {
                                    throw new \RuntimeException("Insufficient stock for {$newProduct->name} (need {$newQty}, have {$newProduct->stock}).");
                                }
                            }
                        } else {
                            // Same product, check quantity diff
                            if ($oldQty !== $newQty && $orderItem->product) {
                                $difference = $newQty - $oldQty;
                                if ($difference > 0) {
                                    // Need more stock — use atomic guard
                                    $affected = Product::where('id', $orderItem->product_id)
                                        ->where('stock', '>=', $difference)
                                        ->decrement('stock', $difference);
                                    if ($affected === 0) {
                                        $productName = $orderItem->product->name ?? 'Product';
                                        throw new \RuntimeException("Insufficient stock for {$productName} (need {$difference} more, have {$orderItem->product->fresh()->stock}).");
                                    }
                                } else {
                                    // Returning stock — safe to increment
                                    $orderItem->product->increment('stock', abs($difference));
                                }
                            }
                        }
                    }

                    $orderItem->update([
                        'product_id' => $newProductId,
                        'quantity' => $newQty,
                        'price_at_purchase' => $unitPrice,
                    ]);
                }
            } else {
                // Create new item
                $newProduct = Product::find($newProductId);
                $newItem = $order->orderItems()->create([
                    'product_id' => $newProductId,
                    'quantity' => $newQty,
                    'price_at_purchase' => $unitPrice,
                    'cost_at_purchase' => $newProduct ? $newProduct->cost_price : 0,
                ]);
                $processedItemIds[] = $newItem->id;

                if ($isStockDeducted && $newProduct) {
                    $affected = Product::where('id', $newProductId)
                        ->where('stock', '>=', $newQty)
                        ->decrement('stock', $newQty);
                    if ($affected === 0) {
                        throw new \RuntimeException("Insufficient stock for {$newProduct->name} (need {$newQty}, have {$newProduct->stock}).");
                    }
                }
            }
        }

        // Handle deleted items
        $itemsToDelete = $order->orderItems()->whereNotIn('id', $processedItemIds)->get();
        foreach ($itemsToDelete as $itemToDelete) {
            if ($isStockDeducted && $itemToDelete->product) {
                $itemToDelete->product->increment('stock', $itemToDelete->quantity);
            }
            $itemToDelete->delete();
        }

        $updateData = [
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'address' => $validated['address'],
            'city' => $validated['city'] ?? $order->city,
            'delivery_charge' => $validated['delivery_charge'],
            'total_amount' => $orderTotal + $validated['delivery_charge'],
            'remarks' => $validated['remarks'] ?? null,
        ];

        if (isset($validated['pathao_city_id'])) $updateData['pathao_city_id'] = $validated['pathao_city_id'];
        if (isset($validated['pathao_zone_id'])) $updateData['pathao_zone_id'] = $validated['pathao_zone_id'];

        if (auth()->user()->role === 'admin' && isset($validated['status']) && $validated['status'] !== $order->status) {
            $orderService = app(OrderService::class);
            $orderService->transitionStatus($order, $validated['status']);
            $order->refresh();
        } elseif (!empty($validated['confirm_order']) && $order->status === 'pending') {
            $orderService = app(OrderService::class);
            $orderService->transitionStatus($order, 'confirmed');
            $order->refresh();
        }

        $order->update($updateData);

        $order->logActivity('full_edit', [
            'notes' => 'Order details and items fully edited by staff.'
        ]);

        }); // end DB::transaction
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Order updated successfully.');
    }

    public function verifyReturn(Request $request, Order $order)
    {
        if ($order->status !== 'return_delivered' || $order->return_verified_at) {
            return response()->json(['success' => false, 'message' => 'Invalid return verification request.'], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|integer',
            'items.*.good_qty' => 'required|integer|min:0',
            'items.*.damaged_qty' => 'required|integer|min:0',
            'return_notes' => 'nullable|string|max:5000',
        ]);

        try {
            $totalGood = 0;
            $totalDamaged = 0;

            DB::transaction(function () use ($order, $validated, &$totalGood, &$totalDamaged) {
                // Validate and update each item
                foreach ($validated['items'] as $itemData) {
                    $orderItem = $order->orderItems()->where('id', $itemData['order_item_id'])->first();

                    if (!$orderItem) {
                        throw new \InvalidArgumentException(
                            "Item #{$itemData['order_item_id']} does not belong to this order."
                        );
                    }

                    $goodQty = (int) $itemData['good_qty'];
                    $damagedQty = (int) $itemData['damaged_qty'];

                    if (($goodQty + $damagedQty) > $orderItem->quantity) {
                        throw new \InvalidArgumentException(
                            "Good ({$goodQty}) + Damaged ({$damagedQty}) exceeds ordered quantity ({$orderItem->quantity}) for item #{$orderItem->id}."
                        );
                    }

                    // Update the order item with return quantities
                    $orderItem->update([
                        'returned_good_qty' => $goodQty,
                        'returned_damaged_qty' => $damagedQty,
                    ]);

                    // Restock ONLY good items
                    if ($goodQty > 0 && $orderItem->product) {
                        $orderItem->product->increment('stock', $goodQty);
                    }

                    $totalGood += $goodQty;
                    $totalDamaged += $damagedQty;
                }

                // NEW-FIN-05: Reverse ALL payment types (Order + Order Delivered), matching saleReturn() logic
                $payments = \App\Models\Transaction::where(function ($q) use ($order) {
                        $q->where(function ($q2) use ($order) {
                            $q2->where('reference_type', SystemAccounts::REF_ORDER)
                               ->where('reference_id', $order->id);
                        })->orWhere(function ($q2) use ($order) {
                            $q2->where('reference_type', SystemAccounts::REF_ORDER_DELIVERED)
                               ->where('reference_id', $order->id);
                        });
                    })
                    ->where('type', 'in')
                    ->get();

                foreach ($payments as $payment) {
                    \App\Models\Transaction::create([
                        'account_id' => $payment->account_id,
                        'type' => 'out',
                        'amount' => $payment->amount,
                        'reference_type' => $payment->reference_type,
                        'reference_id' => $order->id,
                        'date' => now(),
                        'notes' => "Reversal for Returned Order #{$order->id}"
                    ]);

                    $account = \App\Models\Account::find($payment->account_id);
                    if ($account) {
                        // FIN-MED-02: Guard and log if balance would go negative
                        if ($account->balance < $payment->amount) {
                            Log::warning("Account '{$account->name}' balance going negative during return verification", [
                                'account_id' => $account->id,
                                'current_balance' => $account->balance,
                                'deduction' => $payment->amount,
                                'order_id' => $order->id,
                            ]);
                        }
                        $account->decrement('balance', $payment->amount);
                    }
                }

                // Also reverse Pathao party balance if applicable
                $pathaoParty = \App\Models\Party::where('type', 'pathao')->first();
                if ($pathaoParty) {
                    $pathaoTxTotal = $payments->where('reference_type', SystemAccounts::REF_ORDER_DELIVERED)->sum('amount');
                    if ($pathaoTxTotal > 0) {
                        $pathaoParty->decrement('current_balance', $pathaoTxTotal);
                    }
                }

                // Save return notes and mark as verified
                $order->update([
                    'return_verified_at' => now(),
                    'return_notes' => $validated['return_notes'] ?? null,
                ]);

                $order->logActivity('return_verified', [
                    'reversed_payments_count' => $payments->count(),
                    'total_good_qty' => $totalGood,
                    'total_damaged_qty' => $totalDamaged,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Return verified. Restocked: {$totalGood} good items. Damaged: {$totalDamaged} items written off.",
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Return verification failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Return verification failed. Please try again.'], 422);
        }
    }

    /**
     * Damage Report: Lists all returned orders with damaged items for tracking & write-off purposes.
     */
    public function damageReport(Request $request)
    {
        $query = OrderItem::with(['order', 'product'])
            ->where('returned_damaged_qty', '>', 0)
            ->whereHas('order', function ($q) {
                $q->where('status', 'return_delivered')
                  ->whereNotNull('return_verified_at');
            });

        // Date filter
        if ($request->filled('from')) {
            $query->whereHas('order', fn($q) => $q->whereDate('return_verified_at', '>=', $request->from));
        }
        if ($request->filled('to')) {
            $query->whereHas('order', fn($q) => $q->whereDate('return_verified_at', '<=', $request->to));
        }

        $damagedItems = $query->latest('id')->paginate(50)->appends($request->query());

        // Summary stats
        $summaryQuery = OrderItem::where('returned_damaged_qty', '>', 0)
            ->whereHas('order', fn($q) => $q->where('status', 'return_delivered')->whereNotNull('return_verified_at'));

        $totalDamagedQty = (clone $summaryQuery)->sum('returned_damaged_qty');
        $totalDamagedValue = (clone $summaryQuery)->get()->sum(fn($item) => $item->returned_damaged_qty * $item->price_at_purchase);
        $totalDamagedOrders = (clone $summaryQuery)->distinct('order_id')->count('order_id');

        return view('orders.damage-report', [
            'damagedItems' => $damagedItems,
            'totalDamagedQty' => $totalDamagedQty,
            'totalDamagedValue' => $totalDamagedValue,
            'totalDamagedOrders' => $totalDamagedOrders,
        ]);
    }

    public function recordPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cod,paid,partial',
            'amount' => 'required_if:payment_method,paid,partial|numeric|min:0',
            'account_id' => 'required_if:payment_method,paid,partial|exists:accounts,id',
            'notes' => 'nullable|string|max:2000'
        ]);

        if ($validated['payment_method'] === 'cod') {
            $order->logActivity('payment_method_set', [
                'method' => 'COD',
                'notes' => 'Order set to Cash on Delivery.'
            ]);
            return back()->with('success', 'Payment method set to COD.');
        }

        DB::transaction(function () use ($validated, $order) {
            $account = \App\Models\Account::findOrFail($validated['account_id']);
            $amountToPay = $validated['amount'];

            if ($amountToPay > 0) {
                \App\Models\Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'in',
                    'amount' => $amountToPay,
                    'reference_type' => SystemAccounts::REF_ORDER,
                    'reference_id' => $order->id,
                    'date' => now(),
                    'notes' => $validated['notes'] ?: 'Manual Payment for Order #' . $order->id
                ]);
                $account->increment('balance', $amountToPay);
                $order->increment('paid_amount', $amountToPay);
            }
            
            $order->refresh();
            if ($order->paid_amount >= $order->total_amount) {
                $order->update(['payment_status' => 'paid']);
            } elseif ($order->paid_amount > 0) {
                $order->update(['payment_status' => 'partial']);
            }

            $order->logActivity('payment_recorded', [
                'method' => $validated['payment_method'],
                'amount' => $amountToPay,
                'account' => $account->name
            ]);
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * AJAX: Get real-time Pathao tracking details for a shipped order.
     * Uses 2-minute cached data to avoid API flooding.
     */
    public function getPathaoDetails(Order $order, \App\Services\PathaoService $pathao)
    {
        if (!$order->pathao_consignment_id) {
            return response()->json(['error' => 'No Pathao consignment ID found'], 404);
        }

        // Check if data is already in cache (to avoid updating timestamp for stale data)
        $cacheKey = "pathao_order_{$order->pathao_consignment_id}";
        $wasCached = \Illuminate\Support\Facades\Cache::has($cacheKey);

        // Use cached data (2-min TTL) — does not hit Pathao API on every click
        $pathaoData = $pathao->getOrderDetails($order->pathao_consignment_id);
        
        // Only update DB timestamp when we actually fetched fresh data from Pathao
        if (!$wasCached && $pathaoData && isset($pathaoData['order_status'])) {
            $order->update([
                'pathao_status' => $pathaoData['order_status'],
                'pathao_status_updated_at' => now(),
            ]);

            // BUG-FIX: Also transition local status when Pathao reports a terminal state
            // This ensures delivered/returned orders leave the shipped list immediately
            $nonTerminalStatuses = ['shipped', 'delivered', 'failed'];
            if (in_array($order->status, $nonTerminalStatuses)) {
                $normalizedStatus = strtolower($pathaoData['order_status']);
                $newLocalStatus = null;

                if (in_array($normalizedStatus, ['delivered', 'successful'])) {
                    $newLocalStatus = 'delivered';
                } elseif (in_array($normalizedStatus, ['returned', 'return'])) {
                    $newLocalStatus = 'return_delivered';
                } elseif (in_array($normalizedStatus, ['cancelled', 'cancel', 'pickup cancel', 'pickup cancelled'])) {
                    $newLocalStatus = 'rejected';
                }

                if ($newLocalStatus) {
                    $orderService = app(OrderService::class);
                    try {
                        DB::transaction(function () use ($order, $newLocalStatus, $orderService) {
                            $orderService->transitionStatus($order, $newLocalStatus);
                        });
                    } catch (\InvalidArgumentException $e) {
                        Log::warning("Auto-transition failed for Order #{$order->id}: " . $e->getMessage());
                    }
                }
            }
        }

        // Build response combining local + Pathao data
        $order->load('orderItems.product');
        
        return response()->json([
            'order' => [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'address' => $order->address,
                'city' => $order->city,
                'total_amount' => $order->total_amount,
                'delivery_charge' => $order->delivery_charge,
                'paid_amount' => $order->paid_amount ?? 0,
                'payment_status' => $order->payment_status,
                'pathao_consignment_id' => $order->pathao_consignment_id,
                'created_at' => $order->created_at->format('M d, Y g:i A'),
                'shipped_date' => ($order->shipped_at ?? $order->created_at)->format('M d, Y'),
                'items' => $order->orderItems->map(fn($item) => [
                    'name' => $item->product->name ?? 'Unknown Product',
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_purchase,
                    'total' => $item->quantity * $item->price_at_purchase,
                ]),
                'item_count' => $order->orderItems->sum('quantity'),
                'weight_kg' => max(0.5, $order->orderItems->sum(fn($item) => ($item->product->weight_grams ?? 500) * $item->quantity) / 1000),
            ],
            'pathao' => $pathaoData ? [
                'status' => $pathaoData['order_status'] ?? $order->pathao_status ?? 'Unknown',
                'invoice' => $pathaoData['invoice_id'] ?? null,
                'cod_amount' => $pathaoData['amount_to_collect'] ?? null,
                'delivery_fee' => $pathaoData['delivery_fee'] ?? null,
                'cod_charge' => $pathaoData['cod_fee'] ?? null,
                'created_at' => $pathaoData['created_at'] ?? null,
                'updated_at' => $pathaoData['updated_at'] ?? null,
                'picked_at' => $pathaoData['picked_at'] ?? null,
                'delivered_at' => $pathaoData['delivered_at'] ?? null,
                'rider_name' => $pathaoData['delivery_man_name'] ?? $pathaoData['rider_name'] ?? null,
                'rider_phone' => $pathaoData['delivery_man_phone'] ?? $pathaoData['rider_phone'] ?? null,
                'comments' => $pathaoData['comments'] ?? $pathaoData['order_comment'] ?? $pathaoData['special_instruction'] ?? null,
                'failed_reason' => $pathaoData['failed_delivery_comment'] ?? $pathaoData['failed_reason'] ?? null,
            ] : [
                'status' => $order->pathao_status ?? 'Awaiting Pickup',
                'invoice' => null,
                'cod_amount' => null,
                'delivery_fee' => null,
                'cod_charge' => null,
                'created_at' => null,
                'updated_at' => null,
                'picked_at' => null,
                'delivered_at' => null,
                'rider_name' => null,
                'rider_phone' => null,
                'comments' => null,
                'failed_reason' => null,
            ],
            'status_updated_at' => $order->pathao_status_updated_at ? \Carbon\Carbon::parse($order->pathao_status_updated_at)->diffForHumans() : 'Never',
        ]);
    }
}
