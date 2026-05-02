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
        $confirmedOrders = Cache::remember('dashboard_confirmed', 60, function () {
            return Order::with('orderItems.product')->where('status', 'confirmed')->latest()->take(20)->get();
        });
        $shippedOrders = Cache::remember('dashboard_shipped', 60, function () {
            return Order::with('orderItems.product')->where('status', 'shipped')->latest()->take(20)->get();
        });
        
        $products = Product::select('id', 'name', 'price', 'stock', 'image_path')->get();
        
        $lowStockThreshold = (int) setting('low_stock_threshold', 10);
        $lowStockProducts = Product::where('stock', '<', $lowStockThreshold)->get();

        return view('dashboard', compact('pendingOrders', 'confirmedOrders', 'shippedOrders', 'products', 'lowStockProducts'));
    }
}
