<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingOrders = Order::with('orderItems.product')->where('status', 'pending')->latest()->get();
        $confirmedOrders = Order::with('orderItems.product')->where('status', 'confirmed')->latest()->get();
        $shippedOrders = Order::with('orderItems.product')->where('status', 'shipped')->latest()->get();
        
        $products = Product::all();
        
        $lowStockThreshold = (int) setting('low_stock_threshold', 10);
        $lowStockProducts = Product::where('stock', '<', $lowStockThreshold)->get();

        return view('dashboard', compact('pendingOrders', 'confirmedOrders', 'shippedOrders', 'products', 'lowStockProducts'));
    }
}
