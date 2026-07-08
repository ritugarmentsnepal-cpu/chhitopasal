<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE-4: dashboard Business Pulse digest.
 */
class BusinessPulseTest extends TestCase
{
    use RefreshDatabase;

    public function test_pulse_computes_digest_numbers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        // Yesterday: one new order, one delivered
        Order::factory()->create(['created_at' => now()->subDay()->setTime(10, 0)]);
        Order::factory()->create([
            'status' => 'delivered',
            'total_amount' => 5000,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDay()->setTime(15, 0),
        ]);

        // COD in transit: shipped, unpaid
        Order::factory()->create([
            'status' => 'shipped',
            'total_amount' => 2000,
            'delivery_charge' => 100,
            'paid_amount' => 0,
        ]);

        // Stuck: pending for 3 days
        Order::factory()->create(['status' => 'pending', 'created_at' => now()->subDays(3)]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertSuccessful();
        $pulse = $response->viewData('pulse');

        $this->assertEquals(1, $pulse['yesterday_new_orders']);
        $this->assertEquals(1, $pulse['yesterday_delivered_count']);
        $this->assertEquals(5000.0, $pulse['yesterday_delivered_total']);
        $this->assertEquals(2100.0, $pulse['cod_to_collect']);
        $this->assertEquals(1, $pulse['stuck_orders']); // only the 3-day pending order exceeds 48h
        $this->assertStringContainsString('COD to collect', $pulse['share_text']);
    }
}
