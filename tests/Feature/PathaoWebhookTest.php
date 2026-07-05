<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE-1.6: the Pathao webhook can change order statuses (money path) —
 * verify secret enforcement and status mapping.
 *
 * Secret is exercised via the config fallback because the setting() helper
 * caches statically within the process.
 */
class PathaoWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function makeShippedOrder(string $consignment = 'DT123456'): Order
    {
        $order = Order::factory()->create([
            'status' => 'shipped',
            'pathao_consignment_id' => $consignment,
            'shipped_at' => now()->subDay(),
        ]);
        OrderItem::factory()->create(['order_id' => $order->id]);

        return $order;
    }

    public function test_integration_ping_is_acknowledged(): void
    {
        $response = $this->postJson('/webhook/pathao', ['event' => 'webhook_integration']);

        $response->assertStatus(202);
    }

    public function test_delivered_webhook_updates_order_status(): void
    {
        $order = $this->makeShippedOrder();

        $response = $this->postJson('/webhook/pathao', [
            'consignment_id' => 'DT123456',
            'order_status' => 'Delivered',
        ]);

        $response->assertStatus(202);
        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertEquals('Delivered', $order->pathao_status);
    }

    public function test_return_webhook_maps_to_return_delivered(): void
    {
        $order = $this->makeShippedOrder();

        $this->postJson('/webhook/pathao', [
            'consignment_id' => 'DT123456',
            'order_status' => 'Return',
        ])->assertStatus(202);

        $this->assertEquals('return_delivered', $order->fresh()->status);
    }

    public function test_unknown_consignment_returns_404(): void
    {
        $this->postJson('/webhook/pathao', [
            'consignment_id' => 'NOPE999',
            'order_status' => 'Delivered',
        ])->assertStatus(404);
    }

    public function test_wrong_secret_is_rejected_when_configured(): void
    {
        config(['services.pathao.webhook_secret' => 'super-secret']);
        $order = $this->makeShippedOrder();

        $this->postJson('/webhook/pathao', [
            'consignment_id' => 'DT123456',
            'order_status' => 'Delivered',
        ], ['X-Webhook-Secret' => 'wrong'])->assertStatus(401);

        $this->assertEquals('shipped', $order->fresh()->status);
    }

    public function test_correct_secret_is_accepted(): void
    {
        config(['services.pathao.webhook_secret' => 'super-secret']);
        $order = $this->makeShippedOrder();

        $this->postJson('/webhook/pathao', [
            'consignment_id' => 'DT123456',
            'order_status' => 'Delivered',
        ], ['X-Webhook-Secret' => 'super-secret'])->assertStatus(202);

        $this->assertEquals('delivered', $order->fresh()->status);
    }

    public function test_rider_comment_is_logged_from_issue_event(): void
    {
        $order = $this->makeShippedOrder();

        $this->postJson('/webhook/pathao', [
            'consignment_id' => 'DT123456',
            'event' => 'issue',
            'issue_id' => 'ISS-1',
            'issue_description' => 'Customer not answering phone',
        ])->assertStatus(202);

        $this->assertDatabaseHas('rider_comments', [
            'order_id' => $order->id,
            'pathao_issue_id' => 'ISS-1',
            'status' => 'unread',
        ]);
    }
}
