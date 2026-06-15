<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight font-display">
                    {{ __('Sales Dashboard') }}
                </h2>
                <p class="text-sm font-medium text-gray-500 mt-1">Overview of shipped, delivered, and returned sales.</p>
            </div>
            
            <form method="GET" action="{{ route('sales.index') }}" class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-gray-100" x-data="{ filter: '{{ $dateFilter }}' }">
                <select name="date_filter" x-model="filter" @change="if(filter !== 'custom') $el.form.submit()" class="rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-primary focus:ring focus:ring-primary/10 py-2.5 text-sm font-bold text-gray-700">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="custom">Custom Date</option>
                </select>
                
                <template x-if="filter === 'custom'">
                    <div class="flex items-center gap-2">
                        <input type="date" name="from_date" value="{{ $fromDate }}" class="rounded-xl border-gray-200 bg-gray-50 shadow-sm py-2.5 text-sm" required>
                        <span class="text-gray-400">to</span>
                        <input type="date" name="to_date" value="{{ $toDate }}" class="rounded-xl border-gray-200 bg-gray-50 shadow-sm py-2.5 text-sm" required>
                        <button type="submit" class="gradient-bg-vibrant text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-btn hover:shadow-glow transition-all">Apply</button>
                    </div>
                </template>
            </form>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen" x-data="{ activeTab: 'order_wise' }">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Summary Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 stagger-grid">
                <!-- Pending Delivery -->
                <div class="bg-white rounded-2xl p-6 shadow-card border border-blue-100/50 relative overflow-hidden group hover:shadow-[0_20px_40px_rgba(59,130,246,0.1)] transition-all duration-300 animate-fade-up">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1 relative z-10">Pending Delivery (Shipped)</p>
                    <div class="flex items-end gap-3 relative z-10">
                        <p class="text-4xl font-black text-blue-500">{{ $shippedCount }}</p>
                        <p class="text-sm font-bold text-gray-500 mb-1">Orders</p>
                    </div>
                    <p class="text-base font-bold text-gray-900 mt-2 relative z-10">Rs. {{ number_format($shippedAmount) }}</p>
                </div>

                <!-- Delivered -->
                <div class="bg-white rounded-2xl p-6 shadow-card border border-emerald-100/50 relative overflow-hidden group hover:shadow-[0_20px_40px_rgba(16,185,129,0.1)] transition-all duration-300 animate-fade-up" style="animation-delay: 100ms;">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-16 h-16 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1 relative z-10">Delivered</p>
                    <div class="flex items-end gap-3 relative z-10">
                        <p class="text-4xl font-black text-emerald-500">{{ $deliveredCount }}</p>
                        <p class="text-sm font-bold text-gray-500 mb-1">Orders</p>
                    </div>
                    <p class="text-base font-bold text-gray-900 mt-2 relative z-10">Rs. {{ number_format($deliveredAmount) }}</p>
                </div>

                <!-- Returned -->
                <div class="bg-white rounded-2xl p-6 shadow-card border border-red-100/50 relative overflow-hidden group hover:shadow-[0_20px_40px_rgba(239,68,68,0.1)] transition-all duration-300 animate-fade-up" style="animation-delay: 200ms;">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1 relative z-10">Returned</p>
                    <div class="flex items-end gap-3 relative z-10">
                        <p class="text-4xl font-black text-red-500">{{ $returnedCount }}</p>
                        <p class="text-sm font-bold text-gray-500 mb-1">Orders</p>
                    </div>
                    <p class="text-base font-bold text-gray-900 mt-2 relative z-10">Rs. {{ number_format($returnedAmount) }}</p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-4 mb-6 border-b border-gray-200">
                <button @click="activeTab = 'order_wise'" :class="activeTab === 'order_wise' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-3 font-bold text-sm border-b-2 transition-all">
                    Order Wise Sales
                </button>
                <button @click="activeTab = 'product_wise'" :class="activeTab === 'product_wise' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-4 py-3 font-bold text-sm border-b-2 transition-all">
                    Product Wise Sales
                </button>
            </div>

            <!-- Order Wise Tab -->
            <div x-show="activeTab === 'order_wise'" x-transition.opacity>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-black text-gray-400 uppercase tracking-wider">
                                    <th class="px-6 py-4">Order ID</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4">Shipped Date</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Items</th>
                                    <th class="px-6 py-4 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($orders as $order)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('orders.index', ['search' => $order->id]) }}" class="text-primary font-bold hover:underline">
                                                #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-gray-900">{{ $order->customer_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $order->customer_phone }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-gray-900">{{ $order->shipped_at ? $order->shipped_at->format('M d, Y') : '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $order->shipped_at ? $order->shipped_at->format('g:i A') : '' }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($order->status === 'shipped')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Shipped</span>
                                            @elseif($order->status === 'delivered')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Delivered</span>
                                            @elseif($order->status === 'return_delivered')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">Returned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-600">
                                            {{ $order->orderItems->sum('quantity') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-black text-gray-900">Rs. {{ number_format($order->total_amount) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                            <p class="font-bold">No orders found for this period</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($orders->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Product Wise Tab -->
            <div x-show="activeTab === 'product_wise'" x-transition.opacity style="display: none;">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-black text-gray-400 uppercase tracking-wider">
                                    <th class="px-6 py-4">Product Name</th>
                                    <th class="px-6 py-4 text-center" title="Orders pending delivery">Pending Delivery</th>
                                    <th class="px-6 py-4 text-center" title="Orders successfully delivered">Delivered</th>
                                    <th class="px-6 py-4 text-center" title="Orders returned">Returned</th>
                                    <th class="px-6 py-4 text-center border-l border-gray-200" title="Orders with 1 piece of this product">1 Pcs</th>
                                    <th class="px-6 py-4 text-center" title="Orders with 2 pieces of this product">2 Pcs</th>
                                    <th class="px-6 py-4 text-center" title="Orders with 3 pieces of this product">3 Pcs</th>
                                    <th class="px-6 py-4 text-center" title="Orders with 4 or more pieces of this product">4+ Pcs</th>
                                    <th class="px-6 py-4 text-center border-l border-gray-200">Total Orders</th>
                                    <th class="px-6 py-4 text-right">Gross Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($productSales as $stat)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-gray-900">
                                            {{ $stat->name }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-100">{{ $stat->pending_orders }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">{{ $stat->delivered_orders }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-700 font-bold border border-red-100">{{ $stat->returned_orders }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center border-l border-gray-50 font-bold text-gray-600">
                                            {{ $stat->qty_1_orders }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-gray-600">
                                            {{ $stat->qty_2_orders }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-gray-600">
                                            {{ $stat->qty_3_orders }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-gray-600">
                                            {{ $stat->qty_4_plus_orders }}
                                        </td>
                                        <td class="px-6 py-4 text-center border-l border-gray-50 font-black text-gray-800 text-lg">
                                            {{ $stat->total_orders }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-black text-gray-900">Rs. {{ number_format($stat->total_revenue) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-12 text-center text-gray-400">
                                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            <p class="font-bold">No product sales found for this period</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
