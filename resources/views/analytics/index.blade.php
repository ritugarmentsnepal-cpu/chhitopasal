@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Analytics & Ad Performance</h2>
            <p class="text-gray-500 font-medium mt-1">Track your storefront funnel and Facebook Ad traffic.</p>
        </div>
        <form method="GET" action="{{ route('analytics.index') }}" class="flex items-center gap-3">
            <select name="date_filter" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-sm font-bold bg-white focus:ring-blue-500 shadow-sm py-2 px-4">
                <option value="today" {{ $dateFilter == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ $dateFilter == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="this_week" {{ $dateFilter == 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="this_month" {{ $dateFilter == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ $dateFilter == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="all_time" {{ $dateFilter == 'all_time' ? 'selected' : '' }}>All Time</option>
            </select>
        </form>
    </div>

    <!-- Storefront Funnel Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- Visitors -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Visitors</h4>
            <span class="text-3xl font-black text-gray-900">{{ number_format($totalVisitors) }}</span>
        </div>
        
        <div class="hidden md:flex items-center justify-center text-gray-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </div>

        <!-- Explored Products -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </div>
            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Explored</h4>
            <span class="text-3xl font-black text-gray-900">{{ number_format($sessionsWithProductView) }}</span>
        </div>

        <div class="hidden md:flex items-center justify-center text-gray-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </div>

        <!-- Added to Cart -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
            <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Add to Cart</h4>
            <span class="text-3xl font-black text-gray-900">{{ number_format($sessionsWithAddToCart) }}</span>
        </div>
    </div>

    <!-- Final Conversions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-green-600 text-white rounded-3xl p-8 shadow-sm flex flex-col justify-center items-center text-center col-span-1 md:col-span-1">
            <h4 class="text-sm font-bold text-green-100 uppercase tracking-wider mb-2">Total Orders</h4>
            <span class="text-5xl font-black mb-1">{{ number_format($totalOrders) }}</span>
            <div class="text-green-200 font-bold mt-2 bg-green-700/50 px-3 py-1 rounded-full text-sm">
                {{ $conversionRate }}% Conversion Rate
            </div>
        </div>
        
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 col-span-1 md:col-span-2 flex flex-col justify-center items-center text-center">
            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2">Total Revenue Generated</h4>
            <span class="text-5xl font-black text-gray-900">Rs. {{ number_format($totalRevenue, 2) }}</span>
        </div>
    </div>

    <!-- Facebook Ad / UTM Campaigns -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-8">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black text-gray-900">Facebook Ad & Campaign Performance</h3>
                <p class="text-sm text-gray-500 font-medium">Tracking via UTM parameters in your ad URLs.</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </div>
        </div>
        
        @if($campaigns->isEmpty())
        <div class="p-12 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h4 class="text-lg font-bold text-gray-900">No Ad Traffic Found</h4>
            <p class="text-gray-500 mt-1 max-w-md mx-auto">We haven't detected any visitors from Facebook Ads yet. Make sure your Facebook Ads use UTM tracking (e.g. `?utm_campaign=summer_sale`).</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Campaign Name (UTM)</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Clicks / Visitors</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Orders</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Conv. Rate</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($campaigns as $campaign)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-black text-gray-900">{{ $campaign->utm_campaign }}</span>
                        </td>
                        <td class="px-6 py-4 text-center font-medium text-gray-600">
                            {{ number_format($campaign->total_clicks) }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-gray-900">
                            {{ number_format($campaign->orders) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $campaign->conversion_rate > 2 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $campaign->conversion_rate }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-black text-green-600">
                            Rs. {{ number_format($campaign->revenue, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
