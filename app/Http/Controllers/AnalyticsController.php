<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorSession;
use App\Models\VisitorEvent;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $dateFilter = $request->get('date_filter', 'today');

        [$startDate, $endDate] = $this->resolveDateRange($dateFilter);

        // ─── 1. FUNNEL METRICS ───────────────────────────────────────────────
        $totalVisitors = VisitorSession::whereBetween('created_at', [$startDate, $endDate])->count();

        $sessionsWithProductView = VisitorEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'view_product')
            ->distinct('session_id')
            ->count('session_id');

        $sessionsWithAddToCart = VisitorEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'add_to_cart')
            ->distinct('session_id')
            ->count('session_id');

        $sessionsWithCheckout = VisitorEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'initiate_checkout')
            ->distinct('session_id')
            ->count('session_id');

        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id')
            ->count();

        $totalRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('session_id')
            ->sum('total_amount');

        // Conversion Rate
        $conversionRate = $totalVisitors > 0 ? round(($totalOrders / $totalVisitors) * 100, 2) : 0;

        // ─── 2. BOUNCE RATE ──────────────────────────────────────────────────
        // Bounced = session has exactly 1 event (page_view only, never went further)
        $sessionIdsInRange = VisitorSession::whereBetween('created_at', [$startDate, $endDate])
            ->pluck('session_id');

        $bouncedSessions = 0;
        if ($sessionIdsInRange->isNotEmpty()) {
            $bouncedSessions = VisitorEvent::whereIn('session_id', $sessionIdsInRange)
                ->select('session_id', DB::raw('COUNT(*) as event_count'))
                ->groupBy('session_id')
                ->havingRaw('COUNT(*) = 1')
                ->count();
        }
        $bounceRate = $totalVisitors > 0 ? round(($bouncedSessions / $totalVisitors) * 100, 1) : 0;

        // ─── 3. TOP PRODUCTS ─────────────────────────────────────────────────
        $topViewedProducts = VisitorEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'view_product')
            ->whereNotNull('product_id')
            ->select('product_id', DB::raw('COUNT(*) as view_count'))
            ->groupBy('product_id')
            ->orderByDesc('view_count')
            ->limit(10)
            ->with('product:id,name,price')
            ->get();

        $topCartProducts = VisitorEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'add_to_cart')
            ->whereNotNull('product_id')
            ->select('product_id', DB::raw('COUNT(*) as cart_count'))
            ->groupBy('product_id')
            ->orderByDesc('cart_count')
            ->limit(10)
            ->pluck('cart_count', 'product_id');

        // ─── 4. CATEGORY BREAKDOWN (for Facebook Ads) ────────────────────────
        $categoryBreakdown = VisitorEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'add_to_cart')
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderByDesc('count')
            ->with('category:id,name')
            ->get();

        // ─── 5. UTM / FACEBOOK AD CAMPAIGNS ─────────────────────────────────
        $campaigns = VisitorSession::select('utm_campaign', 'utm_source', DB::raw('COUNT(id) as total_clicks'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('utm_campaign')
            ->groupBy('utm_campaign', 'utm_source')
            ->orderByDesc('total_clicks')
            ->get();

        foreach ($campaigns as $campaign) {
            $sessionIds = VisitorSession::whereBetween('created_at', [$startDate, $endDate])
                ->where('utm_campaign', $campaign->utm_campaign)
                ->pluck('session_id');

            $campaign->orders = Order::whereIn('session_id', $sessionIds)->count();
            $campaign->revenue = Order::whereIn('session_id', $sessionIds)->sum('total_amount');
            $campaign->add_to_carts = VisitorEvent::whereIn('session_id', $sessionIds)
                ->where('event_type', 'add_to_cart')->count();
            $campaign->conversion_rate = $campaign->total_clicks > 0
                ? round(($campaign->orders / $campaign->total_clicks) * 100, 2) : 0;
        }

        // ─── 6. FUNNEL CHART DATA (JSON for Chart.js) ────────────────────────
        $funnelData = [
            'labels' => ['Visitors', 'Explored Products', 'Added to Cart', 'Initiated Checkout', 'Orders'],
            'data'   => [$totalVisitors, $sessionsWithProductView, $sessionsWithAddToCart, $sessionsWithCheckout, $totalOrders],
        ];

        $categoryChartData = [
            'labels' => $categoryBreakdown->map(fn($c) => optional($c->category)->name ?? 'Unknown')->toArray(),
            'data'   => $categoryBreakdown->pluck('count')->toArray(),
        ];

        return view('analytics.index', compact(
            'dateFilter',
            'totalVisitors',
            'sessionsWithProductView',
            'sessionsWithAddToCart',
            'sessionsWithCheckout',
            'totalOrders',
            'totalRevenue',
            'conversionRate',
            'bounceRate',
            'topViewedProducts',
            'topCartProducts',
            'categoryBreakdown',
            'campaigns',
            'funnelData',
            'categoryChartData'
        ));
    }

    private function resolveDateRange(string $filter): array
    {
        $end = Carbon::now();
        $start = match($filter) {
            'yesterday'  => Carbon::yesterday()->startOfDay(),
            'this_week'  => Carbon::now()->startOfWeek(),
            'this_month' => Carbon::now()->startOfMonth(),
            'last_month' => Carbon::now()->subMonth()->startOfMonth(),
            'all_time'   => Carbon::create(2020, 1, 1),
            default      => Carbon::today(),
        };
        if ($filter === 'yesterday') {
            $end = Carbon::yesterday()->endOfDay();
        } elseif ($filter === 'last_month') {
            $end = Carbon::now()->subMonth()->endOfMonth();
        }
        return [$start, $end];
    }
}
