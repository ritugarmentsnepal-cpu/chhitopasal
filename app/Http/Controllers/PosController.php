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
 * PHASE-1.5: POS sales, split from OrderController.
 */
class PosController extends Controller
{
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

}
