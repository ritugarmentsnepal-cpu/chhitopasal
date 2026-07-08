<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // PERF-BUG-03: Cache dashboard counts for 60 seconds to reduce DB load
        $pendingOrders = Cache::remember('dashboard_pending', 60, function () {
            return Order::with('orderItems.product')->where('status', 'pending')->latest()->take(20)->get();
        });
        $pendingOrdersCount = Cache::remember('dashboard_pending_count', 60, function () {
            return Order::where('status', 'pending')->count();
        });

        $confirmedOrders = Cache::remember('dashboard_confirmed', 60, function () {
            return Order::with('orderItems.product')->where('status', 'confirmed')->latest()->take(20)->get();
        });
        $confirmedOrdersCount = Cache::remember('dashboard_confirmed_count', 60, function () {
            return Order::where('status', 'confirmed')->count();
        });

        $shippedOrders = Cache::remember('dashboard_shipped', 60, function () {
            return Order::with('orderItems.product')->where('status', 'shipped')->latest()->take(20)->get();
        });
        $shippedOrdersCount = Cache::remember('dashboard_shipped_count', 60, function () {
            return Order::where('status', 'shipped')->count();
        });
        
        $products = Product::select('id', 'name', 'price', 'stock', 'image_path')->get();
        
        $lowStockThreshold = (int) setting('low_stock_threshold', 10);
        $lowStockProducts = Product::where('stock', '<', $lowStockThreshold)->get();

        $pulse = $this->businessPulse($lowStockProducts->count());

        return view('dashboard', compact(
            'pendingOrders', 'pendingOrdersCount',
            'confirmedOrders', 'confirmedOrdersCount',
            'shippedOrders', 'shippedOrdersCount',
            'products', 'lowStockProducts', 'pulse'
        ));
    }

    /**
     * PHASE-4: daily Business Pulse — the digest that used to require
     * manually checking four screens. Shareable to WhatsApp as text.
     */
    protected function businessPulse(int $lowStockCount): array
    {
        $pulse = Cache::remember('dashboard_pulse', 300, function () {
            $yesterday = [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];

            $deliveredYesterday = Order::where('status', 'delivered')
                ->whereBetween('updated_at', $yesterday);

            $codToCollect = Order::where('status', 'shipped')
                ->selectRaw('COALESCE(SUM(total_amount + delivery_charge - paid_amount), 0) as due, COUNT(*) as cnt')
                ->first();

            return [
                'yesterday_delivered_count' => (clone $deliveredYesterday)->count(),
                'yesterday_delivered_total' => (float) (clone $deliveredYesterday)->sum('total_amount'),
                'yesterday_new_orders' => Order::whereBetween('created_at', $yesterday)->count(),
                'cod_to_collect' => (float) $codToCollect->due,
                'cod_orders' => (int) $codToCollect->cnt,
                'stuck_orders' => Order::whereIn('status', ['pending', 'confirmed'])
                    ->where('created_at', '<', now()->subHours(48))
                    ->count(),
            ];
        });

        $pulse['low_stock'] = $lowStockCount;

        $pulse['share_text'] = "📊 " . setting('store_name', 'Chhito Pasal') . " — " . now()->format('M j') . "\n"
            . "Yesterday: {$pulse['yesterday_new_orders']} new orders, {$pulse['yesterday_delivered_count']} delivered (Rs. " . number_format($pulse['yesterday_delivered_total']) . ")\n"
            . "In transit (COD to collect): Rs. " . number_format($pulse['cod_to_collect']) . " across {$pulse['cod_orders']} orders\n"
            . "⚠️ Stuck >48h: {$pulse['stuck_orders']} · Low stock items: {$pulse['low_stock']}";

        return $pulse;
    }
}
