<?php

namespace Tests\Feature;

use App\Models\CustomerLogo;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE-2.2: guided wizard — the studio arrives pre-filled from an order.
 */
class MockupWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_studio_prefills_order_and_matches_customer_logo_by_phone(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $order = Order::factory()->create([
            'customer_name' => 'Sita Sharma',
            'customer_phone' => '9833333333',
            'order_type' => 'custom_print',
        ]);
        $logo = CustomerLogo::create([
            'name' => 'Sita Sharma logo',
            'customer_phone' => '9833333333',
            'file_path' => 'mockup_logos/sita.png',
        ]);

        $response = $this->actingAs($admin)->get('/mockups?order=' . $order->id . '&open=generator');

        $response->assertSuccessful();
        $wizard = $response->viewData('wizard');
        $this->assertEquals($order->id, $wizard['orderId']);
        $this->assertEquals('Sita Sharma — Order #' . $order->id, $wizard['title']);
        $this->assertEquals($logo->id, $wizard['logoId']);
        $this->assertStringContainsString('/orders/' . $order->id, $wizard['returnTo']);
    }

    public function test_wizard_handles_unknown_customer_gracefully(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $order = Order::factory()->create(['customer_phone' => '9800000000']);

        $response = $this->actingAs($admin)->get('/mockups?order=' . $order->id);

        $response->assertSuccessful();
        $this->assertNull($response->viewData('wizard')['logoId']);
    }

    public function test_invalid_order_id_yields_no_wizard(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get('/mockups?order=999999');

        $response->assertSuccessful();
        $this->assertNull($response->viewData('wizard'));
    }
}
