<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\SystemAccounts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ARCH-01: Extracted financial and stock logic from OrderController.
 * Used by both HTTP controllers and console commands.
 */
class OrderService
{
    /**
     * Handle a status transition with all stock and financial side-effects.
     * This is the single source of truth for status changes.
     *
     * @param Order $order
     * @param string $newStatus
     * @return void
     */
    public function transitionStatus(Order $order, string $newStatus): void
    {
        $oldStatus = $order->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $stockDeductedStatuses = ['shipped', 'delivered'];

        // If stock was deducted and we're moving to a non-deducted status
        if (in_array($oldStatus, $stockDeductedStatuses) && in_array($newStatus, ['failed', 'rejected'])) {
            $this->restoreStock($order);
        }

        // If stock was not deducted and we're moving to a deducted status
        if (!in_array($oldStatus, $stockDeductedStatuses) && in_array($newStatus, $stockDeductedStatuses)) {
            $this->deductStock($order);
        }

        // Revenue / Accounts Receivable Logic for Pathao Delivery
        if ($oldStatus !== 'delivered' && $newStatus === 'delivered') {
            $this->recordDeliveryRevenue($order);
        }

        $order->update(['status' => $newStatus]);
    }

    /**
     * Deduct stock for all order items. Uses atomic WHERE clause to prevent negative stock.
     */
    public function deductStock(Order $order): void
    {
        foreach ($order->orderItems as $item) {
            if ($item->product) {
                Product::where('id', $item->product_id)
                    ->where('stock', '>=', $item->quantity)
                    ->decrement('stock', $item->quantity);
            }
        }
    }

    /**
     * Restore stock for all order items.
     */
    public function restoreStock(Order $order): void
    {
        foreach ($order->orderItems as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }

    /**
     * Record delivery revenue in Pathao Clearing account.
     * SEC-12/FIN-01: Guards against duplicate transactions.
     */
    public function recordDeliveryRevenue(Order $order): void
    {
        // Guard: prevent duplicate delivered transactions
        $existingDeliveredTx = Transaction::where('reference_type', SystemAccounts::REF_ORDER_DELIVERED)
            ->where('reference_id', $order->id)
            ->exists();

        if ($existingDeliveredTx) {
            return;
        }

        $pathaoParty = SystemAccounts::pathaoParty();
        $clearingAccount = SystemAccounts::pathaoClearingAccount();

        if ($pathaoParty && $clearingAccount) {
            $dueAmount = $order->total_amount - ($order->paid_amount ?? 0);
            if ($dueAmount > 0) {
                Transaction::create([
                    'account_id' => $clearingAccount->id,
                    'party_id' => $pathaoParty->id,
                    'type' => 'in',
                    'amount' => $dueAmount,
                    'reference_type' => SystemAccounts::REF_ORDER_DELIVERED,
                    'reference_id' => $order->id,
                    'date' => now(),
                    'notes' => "Receivable from Pathao for Order #{$order->id}"
                ]);
                $clearingAccount->increment('balance', $dueAmount);
                $pathaoParty->increment('current_balance', $dueAmount);
            }
        }
    }

    /**
     * Validate stock availability for a list of items.
     * ORD-01/ORD-04: Prevents overselling.
     *
     * @param array $items [['id' => int, 'quantity' => int], ...]
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    public function validateStockAvailability(array $items): array
    {
        $errors = [];

        foreach ($items as $item) {
            $product = Product::find($item['id'] ?? $item['product_id'] ?? null);
            if (!$product) {
                $errors[] = "Product not found.";
                continue;
            }

            $requestedQty = $item['quantity'];
            if ($product->stock < $requestedQty) {
                $errors[] = "{$product->name} only has {$product->stock} in stock (requested {$requestedQty}).";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if a status transition is valid.
     * ORD-02: Prevents invalid transitions that cause stock inflation.
     */
    public function isValidTransition(string $from, string $to): bool
    {
        $validTransitions = [
            'pending' => ['confirmed', 'rejected'],
            'confirmed' => ['shipped', 'pending', 'rejected'],
            'shipped' => ['delivered', 'failed', 'rejected', 'return_delivered'],
            'delivered' => ['return_delivered'],
            'failed' => ['pending', 'confirmed'],
            'rejected' => ['pending'],
            'return_delivered' => [],
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Escape LIKE wildcard characters from user input.
     * SEC-04: Prevents LIKE injection.
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $value);
    }
}
