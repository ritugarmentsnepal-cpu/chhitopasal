<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index()
    {
        // Dynamic CRM: Group by phone number
        $customers = Order::select(
                'customer_phone',
                DB::raw('MAX(customer_name) as latest_name'),
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total_amount) as lifetime_value'),
                DB::raw('MAX(created_at) as last_order_date')
            )
            ->groupBy('customer_phone')
            ->orderByDesc('last_order_date')
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function show($phone)
    {
        // Get all orders for this phone number
        $orders = Order::with('orderItems.product')
            ->where('customer_phone', $phone)
            ->orderByDesc('created_at')
            ->get();

        if ($orders->isEmpty()) {
            abort(404, 'Customer not found.');
        }

        // Aggregate statistics
        $customerData = [
            'phone' => $phone,
            'names' => $orders->pluck('customer_name')->unique()->values()->toArray(),
            'addresses' => $orders->pluck('address')->unique()->values()->toArray(),
            'total_orders' => $orders->count(),
            'lifetime_value' => $orders->sum('total_amount'),
            'first_order_date' => $orders->min('created_at'),
            'last_order_date' => $orders->max('created_at'),
            'orders' => $orders,
        ];

        return view('customers.show', compact('customerData'));
    }
}
