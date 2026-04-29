<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\PathaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('customer_name', 'like', "%$search%")
                  ->orWhere('customer_phone', 'like', "%$search%");
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

        // Pathao delivery status filter (for shipped and post-shipped tabs)
        $pathaoFilter = $request->get('pathao_filter');
        if ($pathaoFilter && in_array($status, ['shipped', 'delivered', 'return_delivered', 'failed', 'rejected'])) {
            if ($pathaoFilter === 'awaiting_pickup') {
                $query->where(function($q) {
                    $q->whereNull('pathao_status')->orWhere('pathao_status', '');
                });
            } else {
                $query->where('pathao_status', 'like', "%{$pathaoFilter}%");
            }
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        // Fetch products for bulk upload reference modal
        $products = Product::all();
        $accounts = \App\Models\Account::all();

        return view('orders.index', compact('orders', 'status', 'products', 'accounts'));
    }

    public function store(Request $request)
    {
        // Existing Manual Store logic (Single Order)
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $totalAmount = $product->price * $validated['quantity'];

        $order = Order::create([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'source' => 'manual',
        ]);

        $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'price_at_purchase' => $product->price,
            'cost_at_purchase' => $product->cost_price,
        ]);

        return redirect()->route('orders.index', ['status' => 'pending'])->with('success', 'Order created manually.');
    }

    public function storePOS(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        $orderItemsData = [];
        
        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $totalAmount += $product->price * $item['quantity'];
            
            $orderItemsData[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price_at_purchase' => $product->price,
                'cost_at_purchase' => $product->cost_price,
            ];
        }

        $order = Order::create([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'address' => 'Store Pickup',
            'city' => 'Local',
            'total_amount' => $totalAmount,
            'status' => 'delivered', // Immediate revenue realization
            'payment_status' => 'paid',
            'paid_amount' => $totalAmount,
            'source' => 'pos',
        ]);

        foreach ($orderItemsData as $data) {
            $order->orderItems()->create($data);
            // Deduct stock immediately
            $product = Product::find($data['product_id']);
            if ($product) {
                $product->decrement('stock', $data['quantity']);
            }
        }

        // Record Transaction
        $cashAccount = \App\Models\Account::where('name', 'Main Cash')->first();
        if ($cashAccount) {
            \App\Models\Transaction::create([
                'account_id' => $cashAccount->id,
                'type' => 'in',
                'amount' => $totalAmount,
                'reference_type' => 'Order',
                'reference_id' => $order->id,
                'date' => now(),
                'notes' => 'POS Cash Sale'
            ]);
            $cashAccount->increment('balance', $totalAmount);
        }

        return redirect()->route('orders.invoice', $order);
    }

    public function invoice(Order $order)
    {
        return view('orders.invoice', compact('order'));
    }

    public function storeWeb(Request $request)
    {
        // Existing Web Store logic
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'delivery_charge' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.color' => 'nullable|string|max:50',
            'items.*.size' => 'nullable|string|max:50',
        ]);

        $totalAmount = 0;
        $orderItemsData = [];

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['id']);
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
            'delivery_charge' => $validated['delivery_charge'],
            'total_amount' => $totalAmount + $validated['delivery_charge'],
            'status' => 'pending',
            'source' => 'web',
        ]);

        $order->orderItems()->createMany($orderItemsData);

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
        foreach ($rows as $row) {
            if (empty($row[0]) || empty($row[4])) continue; // Need name and product
            
            $customerName = trim($row[0]);
            $phone = trim($row[1] ?? '');
            $address = trim($row[2] ?? '');
            $city = trim($row[3] ?? '');
            $productSelection = trim($row[4]);
            
            // Extract ID and Qty using regex: [ID:QTY] Name
            if (preg_match('/\[(\d+):(\d+)\]/', $productSelection, $matches)) {
                $productId = (int)$matches[1];
                $qty = (int)$matches[2];
            } else {
                continue; // Skip invalid formats
            }
            
            $product = Product::find($productId);
            if (!$product) continue;
            
            $itemTotal = $product->price * $qty;
            $unitPrice = $product->price;

            // Check if it's a bundle price
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
            ]);
            
            $order->orderItems()->create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'price_at_purchase' => $unitPrice,
                'cost_at_purchase' => $product->cost_price,
            ]);
            $successCount++;
        }

        return redirect()->back()->with('success', "Bulk uploaded $successCount orders.");
    }

    public function bulkManualStore(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array|min:1',
            'orders.*.customer_name' => 'required|string|max:255',
            'orders.*.customer_phone' => 'required|string|max:20',
            'orders.*.address' => 'required|string|max:255',
            'orders.*.city' => 'nullable|string|max:100',
            'orders.*.product_id' => 'required|exists:products,id',
            'orders.*.quantity' => 'required|integer|min:1',
            'orders.*.amount' => 'required|numeric|min:0',
        ]);

        $successCount = 0;

        foreach ($validated['orders'] as $row) {
            $product = Product::find($row['product_id']);
            if (!$product) continue;

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
            ]);

            $order->orderItems()->create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'price_at_purchase' => $unitPrice,
                'cost_at_purchase' => $product->cost_price,
            ]);

            $successCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully created $successCount orders.",
            'count' => $successCount
        ]);
    }

    public function bulkPrint(Request $request)
    {
        $ids = json_decode($request->input('order_ids', '[]'), true);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No orders selected.');
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
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
        ]);

        // Only allow deleting pending orders
        $orders = Order::whereIn('id', $validated['order_ids'])
                       ->where('status', 'pending')
                       ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No pending orders found to delete.'], 422);
        }

        $deletedCount = 0;
        foreach ($orders as $order) {
            $order->orderItems()->delete();
            $order->delete();
            $deletedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deletedCount} pending orders.",
            'count' => $deletedCount
        ]);
    }

    public function bulkShip(Request $request, PathaoService $pathao)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
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

        foreach ($orders as $order) {
            if (!$order->pathao_city_id || !$order->pathao_zone_id) {
                $failed++;
                $errors[] = "Order #{$order->id}: Missing Pathao location IDs.";
                continue;
            }

            try {
                $result = $pathao->createOrder($order);
                if ($result['success']) {
                    $order->update([
                        'status' => 'shipped',
                        'pathao_consignment_id' => $result['consignment_id'],
                    ]);
                    foreach ($order->orderItems as $item) {
                        if ($item->product) {
                            $item->product->decrement('stock', $item->quantity);
                        }
                    }
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
            'order_ids' => 'required|array|min:1',
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
        foreach ($orders as $order) {
            $oldStatus = $order->status;

            // Handle stock changes
            if (in_array($oldStatus, ['shipped', 'delivered']) && in_array($newStatus, ['failed', 'rejected'])) {
                foreach ($order->orderItems as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update(['status' => $newStatus]);
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} orders to {$newStatus}.",
            'count' => $updated
        ]);
    }

    public function confirm(Request $request, Order $order)
    {
        // This is now handled by fullUpdate. We keep it just in case, but it's redundant.
        return redirect()->back();
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
            $order->update([
                'status' => 'shipped',
                'pathao_consignment_id' => $result['consignment_id'],
            ]);

            // Deduct stock only when shipped
            foreach ($order->orderItems as $item) {
                $product = $item->product;
                if ($product) {
                    $product->decrement('stock', $item->quantity);
                }
            }

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

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // If it was shipped/delivered (stock deducted) and now failed/rejected (stock restored)
        if (in_array($oldStatus, ['shipped', 'delivered']) && in_array($newStatus, ['failed', 'rejected'])) {
            foreach ($order->orderItems as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }
        }
        
        // If it was pending/failed/rejected (stock untouched) and now shipped/delivered (stock deducted)
        if (!in_array($oldStatus, ['shipped', 'delivered']) && in_array($newStatus, ['shipped', 'delivered'])) {
            foreach ($order->orderItems as $item) {
                if ($item->product) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }
        }

        // Revenue / Accounts Receivable Logic for Pathao
        // When Delivered, record as debt owed by Pathao Party (if it's COD or has remaining due)
        if ($oldStatus !== 'delivered' && $newStatus === 'delivered') {
            $pathaoParty = \App\Models\Party::where('type', 'pathao')->first();
            $clearingAccount = \App\Models\Account::where('name', 'Pathao Clearing')->first();
            if ($pathaoParty && $clearingAccount) {
                $dueAmount = $order->total_amount - ($order->paid_amount ?? 0);
                if ($dueAmount > 0) {
                    // Pathao owes us this money — record as receivable in Pathao Clearing account
                    \App\Models\Transaction::create([
                        'account_id' => $clearingAccount->id,
                        'party_id' => $pathaoParty->id,
                        'type' => 'in',
                        'amount' => $dueAmount,
                        'reference_type' => 'Order Delivered',
                        'reference_id' => $order->id,
                        'date' => now(),
                        'notes' => "Receivable from Pathao for Order #{$order->id}"
                    ]);
                    $clearingAccount->increment('balance', $dueAmount);
                    $pathaoParty->increment('current_balance', $dueAmount);
                }
            }
        }

        $order->update(['status' => $newStatus]);

        return redirect()->back()->with('success', 'Order status updated to ' . ucfirst($newStatus));
    }

    public function syncPathaoStatus(Order $order, PathaoService $pathao)
    {
        if (!$order->pathao_consignment_id) {
            return redirect()->back()->with('error', 'Order has no Pathao consignment ID.');
        }

        $status = $pathao->getOrderStatus($order->pathao_consignment_id);

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
            // Re-use the controller's logic to handle stock, finance, and status update
            $request = new Request(['status' => $newLocalStatus]);
            $this->updateStatus($request, $order);
            return redirect()->back()->with('success', 'Pathao status synced successfully. Order is now ' . $newLocalStatus);
        }

        return redirect()->back()->with('success', 'Pathao status is still ' . $status);
    }

    public function masterSyncPathao()
    {
        \Illuminate\Support\Facades\Artisan::call('pathao:sync');
        return redirect()->back()->with('success', 'Master sync completed. ' . \Illuminate\Support\Facades\Artisan::output());
    }

    public function updateAmount(Request $request, Order $order)
    {
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
        if (!in_array($order->status, ['pending', 'confirmed']) && auth()->user()->role !== 'admin') {
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
        ]);

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
                            // Product changed: restore old, deduct new
                            if ($orderItem->product) {
                                $orderItem->product->increment('stock', $oldQty);
                            }
                            $newProduct = Product::find($newProductId);
                            if ($newProduct) {
                                $newProduct->decrement('stock', $newQty);
                            }
                        } else {
                            // Same product, check quantity diff
                            if ($oldQty !== $newQty && $orderItem->product) {
                                $difference = $newQty - $oldQty;
                                $orderItem->product->decrement('stock', $difference);
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
                    $newProduct->decrement('stock', $newQty);
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
        ];

        if (isset($validated['pathao_city_id'])) $updateData['pathao_city_id'] = $validated['pathao_city_id'];
        if (isset($validated['pathao_zone_id'])) $updateData['pathao_zone_id'] = $validated['pathao_zone_id'];

        if (auth()->user()->role === 'admin' && isset($validated['status']) && $validated['status'] !== $order->status) {
            // Admin manual status override. Use existing update logic to handle stock/finance
            $statusReq = new Request(['status' => $validated['status']]);
            $this->updateStatus($statusReq, $order);
            $order->refresh(); // Reload model to reflect status change from updateStatus
        } elseif (!empty($validated['confirm_order']) && $order->status === 'pending') {
            $updateData['status'] = 'confirmed';
        }

        $order->update($updateData);

        $order->logActivity('full_edit', [
            'notes' => 'Order details and items fully edited by staff.'
        ]);

        return back()->with('success', 'Order updated successfully.');
    }

    public function verifyReturn(Request $request, Order $order)
    {
        if ($order->status !== 'return_delivered' || $order->return_verified_at) {
            return back()->with('error', 'Invalid return verification request.');
        }

        $order->update(['return_verified_at' => now()]);

        // Stock Increment
        foreach ($order->orderItems as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        // Automatic Payment Reversal if any payment was recorded
        // Since we are dealing with payments for this order, we reverse any "in" transaction linked to this order.
        $payments = \App\Models\Transaction::where('reference_type', 'Order')
            ->where('reference_id', $order->id)
            ->where('type', 'in')
            ->get();

        foreach ($payments as $payment) {
            // Create negative reversal transaction
            \App\Models\Transaction::create([
                'account_id' => $payment->account_id,
                'type' => 'out',
                'amount' => $payment->amount,
                'reference_type' => 'Order',
                'reference_id' => $order->id,
                'date' => now(),
                'notes' => "Reversal for Returned Order #{$order->id}"
            ]);

            // Adjust account balance
            $account = \App\Models\Account::find($payment->account_id);
            if ($account) {
                $account->decrement('balance', $payment->amount);
            }
        }

        $order->logActivity('return_verified', [
            'reversed_payments_count' => $payments->count()
        ]);

        return back()->with('success', 'Return receipt verified. Stock updated and payments reversed (if any).');
    }

    public function recordPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cod,paid,partial',
            'amount' => 'required_if:payment_method,paid,partial|numeric|min:0',
            'account_id' => 'required_if:payment_method,paid,partial|exists:accounts,id',
            'notes' => 'nullable|string'
        ]);

        // payment_method is tracked via payment_status (unpaid/partial/paid), no separate column needed

        if ($validated['payment_method'] === 'cod') {
            // No cash received now. Status remains pending payment.
            $order->logActivity('payment_method_set', [
                'method' => 'COD',
                'notes' => 'Order set to Cash on Delivery.'
            ]);
            return back()->with('success', 'Payment method set to COD.');
        }

        // For Paid / Partial
        $account = \App\Models\Account::findOrFail($validated['account_id']);
        $amountToPay = $validated['amount'];

        if ($amountToPay > 0) {
            \App\Models\Transaction::create([
                'account_id' => $account->id,
                'type' => 'in',
                'amount' => $amountToPay,
                'reference_type' => 'Order',
                'reference_id' => $order->id,
                'date' => now(),
                'notes' => $validated['notes'] ?: 'Manual Payment for Order #' . $order->id
            ]);
            $account->increment('balance', $amountToPay);
            $order->increment('paid_amount', $amountToPay);
        }
        
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

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * AJAX: Get real-time Pathao tracking details for a shipped order.
     */
    public function getPathaoDetails(Order $order, \App\Services\PathaoService $pathao)
    {
        if (!$order->pathao_consignment_id) {
            return response()->json(['error' => 'No Pathao consignment ID found'], 404);
        }

        // Fetch real-time details from Pathao API
        $pathaoData = $pathao->getOrderDetails($order->pathao_consignment_id);
        
        // Update cached status if we got fresh data
        if ($pathaoData && isset($pathaoData['order_status'])) {
            $order->update([
                'pathao_status' => $pathaoData['order_status'],
                'pathao_status_updated_at' => now(),
            ]);
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
                'shipped_date' => $order->updated_at->format('M d, Y'),
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
