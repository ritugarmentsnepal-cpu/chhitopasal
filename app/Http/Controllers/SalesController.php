<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $dateFilter = $request->input('date_filter', 'this_month');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Apply date filter
        $query = Order::whereIn('status', ['shipped', 'delivered', 'return_delivered']);

        if ($dateFilter === 'today') {
            $query->whereDate('shipped_at', Carbon::today());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('shipped_at', Carbon::yesterday());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('shipped_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($dateFilter === 'this_month') {
            $query->whereBetween('shipped_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($dateFilter === 'custom' && $fromDate && $toDate) {
            $query->whereBetween('shipped_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
        }

        // Clone base query for metrics
        $metricsQuery = clone $query;
        $shippedCount = (clone $metricsQuery)->where('status', 'shipped')->count();
        $shippedAmount = (clone $metricsQuery)->where('status', 'shipped')->sum('total_amount');
        
        $deliveredCount = (clone $metricsQuery)->where('status', 'delivered')->count();
        $deliveredAmount = (clone $metricsQuery)->where('status', 'delivered')->sum('total_amount');
        
        $returnedCount = (clone $metricsQuery)->where('status', 'return_delivered')->count();
        $returnedAmount = (clone $metricsQuery)->where('status', 'return_delivered')->sum('total_amount');

        // Order Wise Sales (Paginated)
        $orders = (clone $query)->with('orderItems.product')->orderBy('shipped_at', 'desc')->paginate(20)->withQueryString();

        // Product Wise Sales (Grouped)
        $productQuery = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['shipped', 'delivered', 'return_delivered']);

        // Re-apply date filter for product query
        if ($dateFilter === 'today') {
            $productQuery->whereDate('orders.shipped_at', Carbon::today());
        } elseif ($dateFilter === 'yesterday') {
            $productQuery->whereDate('orders.shipped_at', Carbon::yesterday());
        } elseif ($dateFilter === 'this_week') {
            $productQuery->whereBetween('orders.shipped_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($dateFilter === 'this_month') {
            $productQuery->whereBetween('orders.shipped_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        } elseif ($dateFilter === 'custom' && $fromDate && $toDate) {
            $productQuery->whereBetween('orders.shipped_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
        }

        $productSales = $productQuery->select(
                'products.name',
                DB::raw('COUNT(DISTINCT CASE WHEN orders.status = "shipped" THEN orders.id END) as pending_orders'),
                DB::raw('COUNT(DISTINCT CASE WHEN orders.status = "delivered" THEN orders.id END) as delivered_orders'),
                DB::raw('COUNT(DISTINCT CASE WHEN orders.status = "return_delivered" THEN orders.id END) as returned_orders'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('COUNT(DISTINCT CASE WHEN order_items.quantity = 1 THEN orders.id END) as qty_1_orders'),
                DB::raw('COUNT(DISTINCT CASE WHEN order_items.quantity = 2 THEN orders.id END) as qty_2_orders'),
                DB::raw('COUNT(DISTINCT CASE WHEN order_items.quantity = 3 THEN orders.id END) as qty_3_orders'),
                DB::raw('COUNT(DISTINCT CASE WHEN order_items.quantity > 3 THEN orders.id END) as qty_4_plus_orders'),
                DB::raw('SUM(order_items.quantity * order_items.price_at_purchase) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_orders')
            ->get();

        return view('sales.index', compact(
            'orders', 
            'productSales', 
            'dateFilter', 
            'fromDate', 
            'toDate',
            'shippedCount', 'shippedAmount',
            'deliveredCount', 'deliveredAmount',
            'returnedCount', 'returnedAmount'
        ));
    }
}
