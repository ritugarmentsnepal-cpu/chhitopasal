<?php

namespace Tests\Feature;

use App\Models\Mockup;
use App\Models\Order;
use App\Models\RiderComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE-3.4: notification center feed + read state.
 */
class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_feed_aggregates_attention_items(): void
    {
        $return = Order::factory()->create(['status' => 'return_delivered', 'return_verified_at' => null]);
        $webOrder = Order::factory()->create(['source' => 'web', 'status' => 'pending']);
        RiderComment::create(['order_id' => $webOrder->id, 'pathao_issue_id' => 'I1', 'rider_comment' => 'Address unclear', 'status' => 'unread']);
        Mockup::create([
            'title' => 'Approved One',
            'image_path' => 'mockups/a.png',
            'order_id' => $webOrder->id,
            'tags' => [],
            'approval_status' => 'approved',
            'approval_responded_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->admin())->getJson('/api/notifications');

        $response->assertSuccessful();
        $types = collect($response->json('items'))->pluck('type');
        $this->assertTrue($types->contains('return'));
        $this->assertTrue($types->contains('order'));
        $this->assertTrue($types->contains('rider'));
        $this->assertTrue($types->contains('approval'));
        $this->assertGreaterThan(0, $response->json('unread'));
    }

    public function test_mark_seen_clears_unread_count(): void
    {
        $admin = $this->admin();
        Order::factory()->create(['status' => 'return_delivered', 'return_verified_at' => null]);

        $this->assertGreaterThan(0, $this->actingAs($admin)->getJson('/api/notifications')->json('unread'));

        $this->actingAs($admin)->postJson('/api/notifications/seen')->assertSuccessful();

        $this->assertEquals(0, $this->actingAs($admin)->getJson('/api/notifications')->json('unread'));
    }

    public function test_order_items_hidden_without_orders_permission(): void
    {
        Order::factory()->create(['status' => 'return_delivered', 'return_verified_at' => null]);

        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
            'permissions' => ['products'],
        ]);

        $types = collect($this->actingAs($staff)->getJson('/api/notifications')->json('items'))->pluck('type');
        $this->assertFalse($types->contains('return'));
    }
}
