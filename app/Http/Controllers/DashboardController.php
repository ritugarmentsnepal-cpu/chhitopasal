<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // PERF-01: Limit to latest 20 per column to prevent page crashes at scale
        $pendingOrders = Order::with('orderItems.product')->where('status', 'pending')->latest()->take(20)->get();
        $confirmedOrders = Order::with('orderItems.product')->where('status', 'confirmed')->latest()->take(20)->get();
        $shippedOrders = Order::with('orderItems.product')->where('status', 'shipped')->latest()->take(20)->get();
        
        $products = Product::all();
        
        $lowStockThreshold = (int) setting('low_stock_threshold', 10);
        $lowStockProducts = Product::where('stock', '<', $lowStockThreshold)->get();

        return view('dashboard', compact('pendingOrders', 'confirmedOrders', 'shippedOrders', 'products', 'lowStockProducts'));
    }
}
