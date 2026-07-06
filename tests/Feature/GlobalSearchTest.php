<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE-3: global search endpoint.
 */
class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_finds_orders_by_phone_and_products_by_name(): void
    {
        Order::factory()->create(['customer_name' => 'Tapeshwar Mukhiya', 'customer_phone' => '9804724516']);
        Product::factory()->create(['name' => 'Polo Tshirt Premium']);

        $response = $this->actingAs($this->admin())->getJson('/api/global-search?q=9804724516');
        $labels = collect($response->json('groups'))->pluck('label');
        $this->assertTrue($labels->contains('Orders'));
        $this->assertTrue($labels->contains('Customers'));

        $response = $this->actingAs($this->admin())->getJson('/api/global-search?q=Polo');
        $this->assertTrue(collect($response->json('groups'))->pluck('label')->contains('Products'));
    }

    public function test_finds_order_by_id(): void
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->admin())->getJson('/api/global-search?q=' . $order->id);

        $orders = collect($response->json('groups'))->firstWhere('label', 'Orders');
        $this->assertNotNull($orders);
        $this->assertStringContainsString("#{$order->id}", $orders['items'][0]['title']);
        $this->assertStringContainsString("/orders/{$order->id}", $orders['items'][0]['url']);
    }

    public function test_results_respect_permissions(): void
    {
        Order::factory()->create(['customer_name' => 'Secret Customer']);
        Product::factory()->create(['name' => 'Secret Product']);

        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
            'permissions' => ['products'], // no orders/customers access
        ]);

        $response = $this->actingAs($staff)->getJson('/api/global-search?q=Secret');
        $labels = collect($response->json('groups'))->pluck('label');

        $this->assertFalse($labels->contains('Orders'));
        $this->assertFalse($labels->contains('Customers'));
        $this->assertTrue($labels->contains('Products'));
    }

    public function test_short_queries_return_nothing(): void
    {
        $this->actingAs($this->admin())->getJson('/api/global-search?q=a')
            ->assertExactJson(['groups' => []]);
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/global-search?q=test')->assertUnauthorized();
    }
}
