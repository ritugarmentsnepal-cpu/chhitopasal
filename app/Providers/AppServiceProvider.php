<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\RiderComment;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // PERF: sidebar badge counts, one cached lookup instead of 3 raw
        // queries on every page load.
        View::composer('layouts.navigation', function ($view) {
            $counts = Cache::remember('nav_badge_counts', 60, function () {
                return [
                    'pendingOrdersCount' => Order::where('status', 'pending')->count(),
                    'openTicketsCount' => SupportTicket::where('status', 'open')->count(),
                    'unreadCommentsCount' => RiderComment::where('status', 'unread')->count(),
                ];
            });

            $view->with($counts);
        });
    }
}
