<?php

namespace Tests\Feature;

use App\Models\Mockup;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE-2.3: customer mockup approval links.
 */
class MockupApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function makeMockup(array $orderAttrs = []): Mockup
    {
        $order = Order::factory()->create(array_merge([
            'order_type' => 'custom_print',
            'status' => 'pending',
            'production_status' => 'design_received',
        ], $orderAttrs));

        return Mockup::create([
            'title' => 'Test Mockup',
            'image_path' => 'mockups/test.png',
            'order_id' => $order->id,
            'tags' => [],
        ]);
    }

    public function test_share_generates_token_and_wa_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $mockup = $this->makeMockup(['customer_phone' => '9812345678']);

        $response = $this->actingAs($admin)->postJson("/mockups/{$mockup->id}/share");

        $response->assertSuccessful();
        $mockup->refresh();
        $this->assertNotNull($mockup->share_token);
        $this->assertEquals('pending', $mockup->approval_status);
        $this->assertStringContainsString('/m/' . $mockup->share_token, $response->json('url'));
        $this->assertStringContainsString('wa.me/9779812345678', $response->json('wa_link'));
    }

    public function test_share_is_idempotent(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $mockup = $this->makeMockup();

        $first = $this->actingAs($admin)->postJson("/mockups/{$mockup->id}/share")->json('url');
        $second = $this->actingAs($admin)->postJson("/mockups/{$mockup->id}/share")->json('url');

        $this->assertEquals($first, $second);
    }

    public function test_public_page_renders_without_auth(): void
    {
        $mockup = $this->makeMockup();
        $mockup->update(['share_token' => 'testtoken123', 'approval_status' => 'pending']);

        $this->get('/m/testtoken123')
            ->assertSuccessful()
            ->assertSee('Test Mockup')
            ->assertSee('Approve This Design');
    }

    public function test_invalid_token_is_404(): void
    {
        $this->get('/m/does-not-exist')->assertNotFound();
    }

    public function test_approval_records_and_advances_production(): void
    {
        $mockup = $this->makeMockup(); // production_status = design_received
        $mockup->update(['share_token' => 'approveme1234', 'approval_status' => 'pending']);

        $response = $this->post('/m/approveme1234', ['decision' => 'approve']);

        $response->assertRedirect();
        $mockup->refresh();
        $this->assertEquals('approved', $mockup->approval_status);
        $this->assertNotNull($mockup->approval_responded_at);
        $this->assertEquals('design_approved', $mockup->order->fresh()->production_status);

        $this->assertDatabaseHas('activity_logs', [
            'model_type' => Order::class,
            'model_id' => $mockup->order_id,
            'action' => 'mockup_approved',
        ]);
    }

    public function test_change_request_records_feedback_without_advancing(): void
    {
        $mockup = $this->makeMockup();
        $mockup->update(['share_token' => 'changeme12345', 'approval_status' => 'pending']);

        $this->post('/m/changeme12345', [
            'decision' => 'request_changes',
            'feedback' => 'Make the logo bigger please',
        ]);

        $mockup->refresh();
        $this->assertEquals('changes_requested', $mockup->approval_status);
        $this->assertEquals('Make the logo bigger please', $mockup->approval_feedback);
        $this->assertEquals('design_received', $mockup->order->fresh()->production_status);
    }

    public function test_approval_is_final(): void
    {
        $mockup = $this->makeMockup();
        $mockup->update(['share_token' => 'finaltoken123']);

        $this->post('/m/finaltoken123', ['decision' => 'approve']);
        $this->post('/m/finaltoken123', ['decision' => 'request_changes', 'feedback' => 'changed my mind']);

        $this->assertEquals('approved', $mockup->fresh()->approval_status);
        $this->assertNull($mockup->fresh()->approval_feedback);
    }

    public function test_approval_on_order_without_production_status_starts_pipeline(): void
    {
        $mockup = $this->makeMockup(['production_status' => null]);
        $mockup->update(['share_token' => 'nullprod12345']);

        $this->post('/m/nullprod12345', ['decision' => 'approve']);

        $this->assertEquals('design_approved', $mockup->order->fresh()->production_status);
    }
}
