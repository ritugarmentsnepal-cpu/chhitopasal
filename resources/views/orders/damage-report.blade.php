<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Damage Report</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Track damaged items from returned orders</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('orders.index', ['status' => 'return_delivered']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Returns
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalDamagedQty }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Damaged Items</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">Rs.{{ number_format($totalDamagedValue) }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Loss Value</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalDamagedOrders }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Affected Orders</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-4 mb-6 shadow-sm">
            <form method="GET" action="{{ route('orders.damageReport') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-2.5 px-3 text-sm font-medium">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-2.5 px-3 text-sm font-medium">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-sm hover:bg-gray-800 transition active:scale-95 text-sm">
                    Filter
                </button>
                @if(request('from') || request('to'))
                    <a href="{{ route('orders.damageReport') }}" class="px-4 py-2.5 text-gray-500 font-bold text-sm hover:text-gray-900 transition">Clear</a>
                @endif
            </form>
        </div>

        <!-- Damage Items Table -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            @if($damagedItems->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Order</th>
                                <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Product</th>
                                <th class="text-center px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Ordered</th>
                                <th class="text-center px-6 py-4 text-[10px] font-black text-green-500 uppercase tracking-widest">Good</th>
                                <th class="text-center px-6 py-4 text-[10px] font-black text-red-500 uppercase tracking-widest">Damaged</th>
                                <th class="text-right px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Loss Value</th>
                                <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Verified</th>
                                <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @foreach($damagedItems as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                                    <!-- Order -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-black text-gray-900 dark:text-white">#{{ str_pad($item->order->id, 5, '0', STR_PAD_LEFT) }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $item->order->customer_name }}</div>
                                    </td>
                                    <!-- Product -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $item->product->name ?? 'Unknown' }}</div>
                                        @if($item->color || $item->size)
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                {{ $item->color }}{{ $item->color && $item->size ? ' / ' : '' }}{{ $item->size }}
                                            </div>
                                        @endif
                                        <div class="text-xs text-gray-400">Rs.{{ number_format($item->price_at_purchase) }}/pc</div>
                                    </td>
                                    <!-- Ordered -->
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $item->quantity }}</span>
                                    </td>
                                    <!-- Good -->
                                    <td class="px-6 py-4 text-center">
                                        @if($item->returned_good_qty > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                                ✅ {{ $item->returned_good_qty }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <!-- Damaged -->
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                            ❌ {{ $item->returned_damaged_qty }}
                                        </span>
                                    </td>
                                    <!-- Loss Value -->
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-black text-red-600">Rs.{{ number_format($item->returned_damaged_qty * $item->price_at_purchase) }}</span>
                                    </td>
                                    <!-- Verified Date -->
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-500 font-medium">
                                            {{ \Carbon\Carbon::parse($item->order->return_verified_at)->format('M d, Y') }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ \Carbon\Carbon::parse($item->order->return_verified_at)->diffForHumans() }}
                                        </div>
                                    </td>
                                    <!-- Notes -->
                                    <td class="px-6 py-4 max-w-[200px]">
                                        @if($item->order->return_notes)
                                            <p class="text-xs text-gray-500 truncate" title="{{ $item->order->return_notes }}">{{ $item->order->return_notes }}</p>
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $damagedItems->links() }}
                </div>
            @else
                <div class="p-16 text-center">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">No Damaged Items</h3>
                    <p class="text-sm text-gray-500 mt-1">No damaged items recorded from returned orders{{ request('from') || request('to') ? ' in the selected period' : '' }}.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
