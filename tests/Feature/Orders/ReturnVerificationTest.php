<?php

namespace Tests\Feature\Orders;

use App\Models\Account;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\SystemAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE-1.6: money-path tests for return verification —
 * selective restocking and payment reversal.
 */
class ReturnVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    protected function makeReturnedOrder(int $stock = 5, int $qty = 3): Order
    {
        $product = Product::factory()->create(['stock' => $stock]);
        $order = Order::factory()->create(['status' => 'return_delivered', 'total_amount' => 3000]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'price_at_purchase' => 1000,
        ]);

        return $order->fresh();
    }

    public function test_only_good_items_are_restocked(): void
    {
        $order = $this->makeReturnedOrder(stock: 5, qty: 3);
        $item = $order->orderItems->first();

        $response = $this->actingAs($this->admin)->postJson("/orders/{$order->id}/verify-return", [
            'items' => [
                ['order_item_id' => $item->id, 'good_qty' => 2, 'damaged_qty' => 1],
            ],
            'return_notes' => 'One piece torn',
        ]);

        $response->assertSuccessful();
        $item->refresh();
        $this->assertEquals(2, $item->returned_good_qty);
        $this->assertEquals(1, $item->returned_damaged_qty);
        $this->assertEquals(7, $item->product->fresh()->stock); // 5 + 2 good only
        $this->assertNotNull($order->fresh()->return_verified_at);
    }

    public function test_return_reverses_recorded_payments(): void
    {
        $order = $this->makeReturnedOrder();
        $item = $order->orderItems->first();

        $account = Account::create(['name' => 'Return Cash', 'type' => 'cash', 'balance' => 3000]);
        Transaction::create([
            'account_id' => $account->id,
            'type' => 'in',
            'amount' => 3000,
            'reference_type' => SystemAccounts::REF_ORDER,
            'reference_id' => $order->id,
            'date' => now(),
        ]);

        $this->actingAs($this->admin)->postJson("/orders/{$order->id}/verify-return", [
            'items' => [
                ['order_item_id' => $item->id, 'good_qty' => 3, 'damaged_qty' => 0],
            ],
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'type' => 'out',
            'amount' => 3000,
            'reference_id' => $order->id,
        ]);
        $this->assertEquals(0, (float) $account->fresh()->balance);
    }

    public function test_return_quantities_cannot_exceed_ordered_quantity(): void
    {
        $order = $this->makeReturnedOrder(stock: 5, qty: 3);
        $item = $order->orderItems->first();

        $response = $this->actingAs($this->admin)->postJson("/orders/{$order->id}/verify-return", [
            'items' => [
                ['order_item_id' => $item->id, 'good_qty' => 3, 'damaged_qty' => 2], // 5 > 3
            ],
        ]);

        $this->assertNull($order->fresh()->return_verified_at);
        $this->assertEquals(5, $item->product->fresh()->stock); // unchanged
    }

    public function test_cannot_verify_twice(): void
    {
        $order = $this->makeReturnedOrder();
        $item = $order->orderItems->first();

        $payload = ['items' => [['order_item_id' => $item->id, 'good_qty' => 1, 'damaged_qty' => 0]]];

        $this->actingAs($this->admin)->postJson("/orders/{$order->id}/verify-return", $payload)->assertSuccessful();
        $this->actingAs($this->admin)->postJson("/orders/{$order->id}/verify-return", $payload)->assertStatus(422);

        $this->assertEquals(6, $item->product->fresh()->stock); // restocked once only (5 + 1)
    }
}
