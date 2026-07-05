<?php

namespace Tests\Feature\Orders;

use App\Models\Account;
use App\Models\Order;
use App\Models\User;
use App\SystemAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE-1.6: money-path tests for recording payments against orders.
 */
class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->account = Account::create(['name' => 'Test Cash', 'type' => 'cash', 'balance' => 1000]);
    }

    public function test_full_payment_creates_transaction_and_marks_paid(): void
    {
        $order = Order::factory()->create(['total_amount' => 2000, 'paid_amount' => 0]);

        $response = $this->actingAs($this->admin)->post("/orders/{$order->id}/payment", [
            'payment_method' => 'paid',
            'amount' => 2000,
            'account_id' => $this->account->id,
        ]);

        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals(2000, (float) $order->paid_amount);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals(3000, (float) $this->account->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'account_id' => $this->account->id,
            'type' => 'in',
            'amount' => 2000,
            'reference_type' => SystemAccounts::REF_ORDER,
            'reference_id' => $order->id,
        ]);
    }

    public function test_partial_payment_marks_partial(): void
    {
        $order = Order::factory()->create(['total_amount' => 2000, 'paid_amount' => 0]);

        $this->actingAs($this->admin)->post("/orders/{$order->id}/payment", [
            'payment_method' => 'partial',
            'amount' => 500,
            'account_id' => $this->account->id,
        ]);

        $order->refresh();
        $this->assertEquals(500, (float) $order->paid_amount);
        $this->assertEquals('partial', $order->payment_status);
        $this->assertEquals(1500, (float) $this->account->fresh()->balance);
    }

    public function test_cod_records_no_transaction(): void
    {
        $order = Order::factory()->create(['total_amount' => 2000]);

        $response = $this->actingAs($this->admin)->post("/orders/{$order->id}/payment", [
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('transactions', [
            'reference_type' => SystemAccounts::REF_ORDER,
            'reference_id' => $order->id,
        ]);
        $this->assertEquals(1000, (float) $this->account->fresh()->balance);
    }

    public function test_staff_without_orders_permission_cannot_record_payment(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
            'permissions' => ['products'], // no "orders"
        ]);
        $order = Order::factory()->create(['total_amount' => 2000]);

        $response = $this->actingAs($staff)->post("/orders/{$order->id}/payment", [
            'payment_method' => 'paid',
            'amount' => 2000,
            'account_id' => $this->account->id,
        ]);

        $response->assertStatus(302); // redirected away by permission middleware
        $this->assertEquals(0, (float) $order->fresh()->paid_amount);
    }
}
