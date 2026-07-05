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
        $orderType = $request->get('order_type', 'standard');
        if (!in_array($orderType, ['standard', 'custom_print'])) {
            $orderType = 'standard';
        }

        $search = $request->get('search');
        $dateFilter = $request->get('date_filter');

        $status = $request->get('status');
        
        $query = Order::with('orderItems.product')->where('order_type', $orderType);

        if ($orderType === 'custom_print') {
            $validStatuses = ['pending', 'design', 'production', 'ready_to_ship', 'shipped', 'delivered', 'rejected'];
            if (!$status || !in_array($status, $validStatuses)) {
                $status = 'pending';
            }

            if ($status === 'pending') {
                $query->where('status', 'pending')
                      ->whereNull('production_status');
            } elseif ($status === 'design') {
                $query->whereIn('status', ['pending', 'confirmed'])
                      ->whereIn('production_status', ['design_received', 'design_approved']);
            } elseif ($status === 'production') {
                $query->whereIn('status', ['pending', 'confirmed'])
                      ->whereIn('production_status', ['in_production', 'quality_check']);
            } elseif ($status === 'ready_to_ship') {
                $query->where('status', 'confirmed')
                      ->where('production_status', 'ready_to_ship');
            } else {
                // shipped, delivered, rejected fall back to standard status filtering
                $query->where('status', $status);
            }
        } else {
            $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'failed', 'rejected', 'return_delivered'];
            if (!$status || !in_array($status, $validStatuses)) {
                $status = 'pending';
            }
            $query->where('status', $status);
        }

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

        return view('orders.index', compact('orders', 'status', 'products', 'accounts', 'orderType'));
    }

    /**
     * PHASE-1.1: Dedicated order detail page — timeline, items, payments,
     * shipping, custom-print pipeline. Replaces working out of list modals.
     */
    public function show(Order $order)
    {
        $order->load([
            'orderItems.product',
            'transactions.account',
            'libraryMockups',
        ]);

        $timeline = $order->activityLogs()
            ->with('user')
            ->latest()
            ->limit(50)
            ->get();

        $riderComments = \App\Models\RiderComment::where('order_id', $order->id)
            ->latest()
            ->get();

        $accounts = \App\Models\Account::orderBy('name')->get();

        return view('orders.show', compact('order', 'timeline', 'riderComments', 'accounts'));
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
}
