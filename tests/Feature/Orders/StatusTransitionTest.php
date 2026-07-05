<?php

namespace Tests\Feature\Orders;

use App\Events\OrderStatusChanged;
use App\Models\Account;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Party;
use App\Models\Product;
use App\Models\User;
use App\SystemAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * PHASE-1.6: money-path tests for order status transitions —
 * stock movement, revenue recording, transition guards, events.
 */
class StatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    protected function makeOrder(string $status = 'pending', int $stock = 10, int $qty = 2): Order
    {
        $product = Product::factory()->create(['stock' => $stock]);
        $order = Order::factory()->create(['status' => $status]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $qty,
        ]);

        return $order->fresh();
    }

    public function test_pending_to_confirmed_succeeds_and_fires_event(): void
    {
        Event::fake([OrderStatusChanged::class]);
        $order = $this->makeOrder('pending');

        $response = $this->actingAs($this->admin)
            ->post("/orders/{$order->id}/status", ['status' => 'confirmed']);

        $response->assertSessionHas('success');
        $this->assertEquals('confirmed', $order->fresh()->status);

        Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order) {
            return $event->order->id === $order->id
                && $event->from === 'pending'
                && $event->to === 'confirmed';
        });
    }

    public function test_shipping_deducts_stock_and_sets_shipped_at(): void
    {
        $order = $this->makeOrder('confirmed', stock: 10, qty: 2);

        $this->actingAs($this->admin)
            ->post("/orders/{$order->id}/status", ['status' => 'shipped']);

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
        $this->assertNotNull($order->shipped_at);
        $this->assertEquals(8, $order->orderItems->first()->product->stock);
    }

    public function test_rejecting_shipped_order_restores_stock(): void
    {
        $order = $this->makeOrder('confirmed', stock: 10, qty: 2);

        $this->actingAs($this->admin)
            ->post("/orders/{$order->id}/status", ['status' => 'shipped']);
        $this->assertEquals(8, $order->fresh()->orderItems->first()->product->stock);

        $this->actingAs($this->admin)
            ->post("/orders/{$order->id}/status", ['status' => 'rejected']);

        $this->assertEquals('rejected', $order->fresh()->status);
        $this->assertEquals(10, $order->fresh()->orderItems->first()->product->stock);
    }

    public function test_invalid_transition_is_blocked(): void
    {
        $order = $this->makeOrder('delivered');

        $response = $this->actingAs($this->admin)
            ->post("/orders/{$order->id}/status", ['status' => 'pending']);

        $response->assertSessionHas('error');
        $this->assertEquals('delivered', $order->fresh()->status);
    }

    public function test_delivery_records_receivable_in_pathao_clearing(): void
    {
        // System records revenue only when the Pathao party + clearing account exist
        Party::create([
            'name' => 'Pathao',
            'type' => SystemAccounts::PATHAO_PARTY_TYPE,
            'current_balance' => 0,
        ]);
        $clearing = Account::create([
            'name' => SystemAccounts::PATHAO_CLEARING,
            'type' => 'bank',
            'balance' => 0,
        ]);

        $order = $this->makeOrder('confirmed');
        $order->update(['total_amount' => 2000, 'paid_amount' => 0]);

        $this->actingAs($this->admin)->post("/orders/{$order->id}/status", ['status' => 'shipped']);
        $this->actingAs($this->admin)->post("/orders/{$order->id}/status", ['status' => 'delivered']);

        $this->assertEquals('delivered', $order->fresh()->status);
        $this->assertDatabaseHas('transactions', [
            'reference_type' => SystemAccounts::REF_ORDER_DELIVERED,
            'reference_id' => $order->id,
            'type' => 'in',
            'amount' => 2000,
        ]);
        $this->assertEquals(2000, (float) $clearing->fresh()->balance);
    }

    public function test_delivery_revenue_is_not_recorded_twice(): void
    {
        Party::create(['name' => 'Pathao', 'type' => SystemAccounts::PATHAO_PARTY_TYPE, 'current_balance' => 0]);
        Account::create(['name' => SystemAccounts::PATHAO_CLEARING, 'type' => 'bank', 'balance' => 0]);

        $order = $this->makeOrder('confirmed');
        $this->actingAs($this->admin)->post("/orders/{$order->id}/status", ['status' => 'shipped']);
        $this->actingAs($this->admin)->post("/orders/{$order->id}/status", ['status' => 'delivered']);

        // Attempt to re-trigger via the service directly (idempotency guard)
        app(\App\Services\OrderService::class)->recordDeliveryRevenue($order->fresh());

        $this->assertEquals(1, \App\Models\Transaction::where('reference_type', SystemAccounts::REF_ORDER_DELIVERED)
            ->where('reference_id', $order->id)
            ->count());
    }
}
