<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorSession;
use App\Models\VisitorEvent;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $dateFilter = $request->get('date_filter', 'today');
        
        $startDate = Carbon::today();
        $endDate = Carbon::now();

        if ($dateFilter === 'yesterday') {
            $startDate = Carbon::yesterday();
            $endDate = Carbon::yesterday()->endOfDay();
        } elseif ($dateFilter === 'this_week') {
            $startDate = Carbon::now()->startOfWeek();
        } elseif ($dateFilter === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
        } elseif ($dateFilter === 'last_month') {
            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate = Carbon::now()->subMonth()->endOfMonth();
        } elseif ($dateFilter === 'all_time') {
            $startDate = Carbon::create(2020, 1, 1);
        }

        // 1. Funnel Metrics
        $totalVisitors = VisitorSession::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $sessionsWithProductView = VisitorEvent::whereBetween('created_at', [$startDate, $endDate])
                                                ->where('event_type', 'view_product')
                                                ->distinct('session_id')
                                                ->count('session_id');

        $sessionsWithAddToCart = VisitorEvent::whereBetween('created_at', [$startDate, $endDate])
                                                ->where('event_type', 'add_to_cart')
                                                ->distinct('session_id')
                                                ->count('session_id');

        // Revenue & Orders linked to sessions
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])
                            ->whereNotNull('session_id')
                            ->count();
                            
        $totalRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
                            ->whereNotNull('session_id')
                            ->sum('total_amount');

        // Conversion Rate
        $conversionRate = $totalVisitors > 0 ? round(($totalOrders / $totalVisitors) * 100, 2) : 0;

        // 2. Facebook Ad / UTM Campaign Performance
        $campaigns = VisitorSession::select('utm_campaign', DB::raw('COUNT(id) as total_clicks'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('utm_campaign')
            ->groupBy('utm_campaign')
            ->orderByDesc('total_clicks')
            ->get();

        // Calculate Revenue per campaign manually since it's a join
        foreach ($campaigns as $campaign) {
            $sessionIds = VisitorSession::whereBetween('created_at', [$startDate, $endDate])
                                        ->where('utm_campaign', $campaign->utm_campaign)
                                        ->pluck('session_id');
            
            $campaign->orders = Order::whereIn('session_id', $sessionIds)->count();
            $campaign->revenue = Order::whereIn('session_id', $sessionIds)->sum('total_amount');
            $campaign->conversion_rate = $campaign->total_clicks > 0 ? round(($campaign->orders / $campaign->total_clicks) * 100, 2) : 0;
        }

        return view('analytics.index', compact(
            'dateFilter',
            'totalVisitors',
            'sessionsWithProductView',
            'sessionsWithAddToCart',
            'totalOrders',
            'totalRevenue',
            'conversionRate',
            'campaigns'
        ));
    }
}
