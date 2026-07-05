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

/**
 * PHASE-1.5: Bulk order tooling (spreadsheet entry, CSV upload, bulk
 * print/delete/ship/status, batch history), split from OrderController.
 */
class OrderBulkController extends Controller
{
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



}
