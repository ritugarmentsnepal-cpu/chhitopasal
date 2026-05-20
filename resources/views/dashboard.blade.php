<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
                    {{ __('Dashboard') }}
                </h2>
                <p class="text-sm font-bold text-gray-500 mt-1">Here's what's happening today.</p>
            </div>
            <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'manual-order-modal')" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 transition duration-150 active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden sm:inline">Manual Order</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen" x-data="{ activeTab: 'pending', isMobile: window.innerWidth < 768 }" x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 768 })">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-50 text-green-700 px-6 py-4 rounded-2xl shadow-sm border border-green-100 flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <div class="bg-green-100 p-1.5 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
                <div class="mb-6 bg-red-50 text-red-700 px-6 py-4 rounded-2xl shadow-sm border border-red-100 flex flex-col sm:flex-row sm:items-center gap-4 animate-[fadeInUp_0.3s_ease-out]">
                    <div class="flex items-center gap-3 font-bold">
                        <div class="bg-red-100 p-1.5 rounded-full animate-pulse">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <span>Low Stock Alert ({{ $lowStockProducts->count() }} items):</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($lowStockProducts->take(4) as $product)
                            <span class="bg-white text-red-700 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm border border-red-100">{{ $product->name }} ({{ $product->stock }})</span>
                        @endforeach
                        @if($lowStockProducts->count() > 4)
                            <span class="bg-red-100 text-red-800 text-xs font-black px-3 py-1.5 rounded-lg">+{{ $lowStockProducts->count() - 4 }}</span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Analytics Widgets (Clean, No Sparklines) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                <!-- Pending -->
                <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 sm:p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 relative overflow-hidden group hover:shadow-[0_12px_40px_rgb(0,0,0,0.07)] transition-shadow">
                    <div class="absolute top-0 right-0 p-3 opacity-[0.07] group-hover:opacity-[0.12] transition-opacity">
                        <svg class="w-14 h-14 text-mango" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 relative z-10">Pending</p>
                    <p class="text-3xl sm:text-4xl font-black text-gray-900 relative z-10">{{ $pendingOrdersCount }}</p>
                </div>

                <!-- Ready to Ship -->
                <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 sm:p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 relative overflow-hidden group hover:shadow-[0_12px_40px_rgb(0,0,0,0.07)] transition-shadow">
                    <div class="absolute top-0 right-0 p-3 opacity-[0.07] group-hover:opacity-[0.12] transition-opacity">
                        <svg class="w-14 h-14 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 relative z-10">Ready to Ship</p>
                    <p class="text-3xl sm:text-4xl font-black text-blue-500 relative z-10">{{ $confirmedOrdersCount }}</p>
                </div>

                <!-- Shipped -->
                <div class="bg-white dark:bg-gray-900 rounded-[24px] p-5 sm:p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 relative overflow-hidden group hover:shadow-[0_12px_40px_rgb(0,0,0,0.07)] transition-shadow">
                    <div class="absolute top-0 right-0 p-3 opacity-[0.07] group-hover:opacity-[0.12] transition-opacity">
                        <svg class="w-14 h-14 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 relative z-10">Shipped</p>
                    <p class="text-3xl sm:text-4xl font-black text-green-500 relative z-10">{{ $shippedOrdersCount }}</p>
                </div>

                <!-- Pipeline Value -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-[24px] p-5 sm:p-6 shadow-[0_10px_40px_rgb(17,24,39,0.2)] border border-gray-700 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 w-28 h-28 bg-mango/20 rounded-full blur-2xl group-hover:bg-mango/30 transition-colors"></div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 relative z-10">Pipeline Value</p>
                    <p class="text-2xl sm:text-3xl font-black text-white relative z-10 truncate">
                        Rs.{{ number_format($pendingOrders->sum('total_amount') + $confirmedOrders->sum('total_amount') + $shippedOrders->sum('total_amount')) }}
                    </p>
                </div>
            </div>

            <!-- Quick Actions (Compact Toolbar) -->
            @if(in_array(auth()->user()->role, ['admin', 'manager', 'accountant']))
            <div class="mb-8 bg-white dark:bg-gray-900 p-2 sm:p-3 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-gray-100 flex overflow-x-auto no-scrollbar gap-2">
                <a href="{{ route('accounting.index') }}" class="flex-shrink-0 flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Financial Dashboard
                </a>
                <div class="w-px h-8 bg-gray-200 my-auto hidden sm:block"></div>
                <a href="{{ route('purchases.index') }}" class="flex-shrink-0 flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Record Purchase
                </a>
                <div class="w-px h-8 bg-gray-200 my-auto hidden sm:block"></div>
                <a href="{{ route('expenses.index') }}" class="flex-shrink-0 flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Record Expense
                </a>
            </div>
            @endif

            <!-- Mobile Tabs -->
            <div class="md:hidden flex gap-2 mb-6 overflow-x-auto pb-2 no-scrollbar">
                <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'bg-mango text-gray-900 shadow-md' : 'bg-white text-gray-500 border border-gray-200'" class="px-6 py-2.5 rounded-full font-bold whitespace-nowrap transition-all">Pending ({{ $pendingOrdersCount }})</button>
                <button @click="activeTab = 'confirmed'" :class="activeTab === 'confirmed' ? 'bg-blue-500 text-white shadow-md' : 'bg-white text-gray-500 border border-gray-200'" class="px-6 py-2.5 rounded-full font-bold whitespace-nowrap transition-all">Confirmed ({{ $confirmedOrdersCount }})</button>
                <button @click="activeTab = 'shipped'" :class="activeTab === 'shipped' ? 'bg-green-500 text-white shadow-md' : 'bg-white text-gray-500 border border-gray-200'" class="px-6 py-2.5 rounded-full font-bold whitespace-nowrap transition-all">Shipped ({{ $shippedOrdersCount }})</button>
            </div>

            <!-- Kanban Board -->
            <div class="flex flex-col md:flex-row gap-6 items-start overflow-x-auto pb-8 -mx-4 px-4 sm:mx-0 sm:px-0">
                
                <!-- Column: Pending -->
                <div class="w-full md:w-[400px] shrink-0 bg-gray-100/50 dark:bg-gray-800/30 rounded-[32px] p-4 border border-gray-200/60" x-show="!isMobile || activeTab === 'pending'">
                    <div class="flex items-center justify-between mb-4 px-2">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 bg-mango rounded-full shadow-[0_0_8px_#FFD166] animate-pulse"></div>
                            <h3 class="text-base font-black text-gray-900 uppercase tracking-widest">Pending</h3>
                        </div>
                        <span class="bg-white px-2.5 py-0.5 rounded-full text-xs font-bold text-gray-500 shadow-sm">{{ $pendingOrdersCount }}</span>
                    </div>

                    <div class="flex flex-col gap-3">
                        @forelse($pendingOrders as $order)
                            <div class="bg-white dark:bg-gray-900 rounded-[20px] p-5 shadow-[0_4px_15px_rgb(0,0,0,0.02)] border border-white hover:border-mango/30 hover:shadow-[0_8px_25px_rgb(0,0,0,0.06)] transition-all">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 mb-1 uppercase tracking-wider">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        <h4 class="font-bold text-base text-gray-900 dark:text-white leading-tight">{{ $order->customer_name }}</h4>
                                    </div>
                                    <span class="bg-gray-50 text-gray-500 border border-gray-100 text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-md">{{ $order->source }}</span>
                                </div>
                                
                                <div class="text-sm text-gray-500 mb-3 space-y-1">
                                    <p class="flex items-center gap-2"><svg class="h-3.5 w-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" /></svg> <span class="font-medium">{{ $order->customer_phone }}</span></p>
                                    <p class="flex items-center gap-2 line-clamp-1"><svg class="h-3.5 w-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg> <span class="font-medium">{{ $order->address }}, {{ $order->city }}</span></p>
                                </div>
                                
                                <div class="flex items-center justify-between border-t border-gray-50 pt-3 mb-3">
                                    <span class="text-xs font-bold text-gray-400">{{ $order->orderItems->sum('quantity') }} items</span>
                                    <span class="font-black text-mango">Rs.{{ number_format($order->total_amount) }}</span>
                                </div>

                                <!-- Always visible button -->
                                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-order-modal-{{ $order->id }}')" class="w-full bg-gray-50 text-gray-700 font-bold py-2.5 rounded-xl text-sm hover:bg-gray-900 hover:text-white transition-all duration-200 active:scale-[0.98]">
                                    Verify Details
                                </button>
                            </div>
                            
                            <!-- Confirm Order Modal -->
                            <x-modal name="confirm-order-modal-{{ $order->id }}" focusable>
                                <form method="POST" action="{{ route('orders.status', $order) }}" class="p-8">
                                    @csrf
                                    <input type="hidden" name="status" value="confirmed">
                                    <div class="mb-6">
                                        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Verify Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h2>
                                        <p class="text-sm font-medium text-gray-500 mt-1">Confirm quantities before sending to Pathao.</p>
                                    </div>
                                    
                                    <div class="space-y-3 mb-8">
                                        @foreach($order->orderItems as $item)
                                            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-100">
                                                <div>
                                                    <p class="font-bold text-gray-900 text-sm">{{ $item->product->name ?? 'Unknown Product' }}</p>
                                                    <p class="text-xs text-gray-500 font-bold mt-0.5">Rs.{{ number_format($item->price_at_purchase) }} × {{ $item->quantity }}</p>
                                                </div>
                                                <span class="font-black text-gray-900 bg-white px-3 py-1 rounded-lg shadow-sm">{{ $item->quantity }} pcs</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex justify-end gap-3">
                                        <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                                        <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 shadow-lg active:scale-95 transition">Confirm Order</button>
                                    </div>
                                </form>
                            </x-modal>
                        @empty
                            <div class="rounded-[20px] p-8 text-center text-gray-400 bg-white border border-dashed border-gray-200">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="font-bold text-sm">No pending orders</p>
                            </div>
                        @endforelse

                        @if($pendingOrdersCount > 20)
                            <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="block text-center text-xs font-bold text-gray-500 hover:text-mango transition-colors py-2 uppercase tracking-wider">
                                View all {{ $pendingOrdersCount }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Column: Confirmed -->
                <div class="w-full md:w-[400px] shrink-0 bg-gray-100/50 dark:bg-gray-800/30 rounded-[32px] p-4 border border-gray-200/60" x-show="!isMobile || activeTab === 'confirmed'">
                    <div class="flex items-center justify-between mb-4 px-2">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 bg-blue-500 rounded-full shadow-[0_0_8px_rgba(59,130,246,0.6)]"></div>
                            <h3 class="text-base font-black text-gray-900 uppercase tracking-widest">Confirmed</h3>
                        </div>
                        <span class="bg-white px-2.5 py-0.5 rounded-full text-xs font-bold text-gray-500 shadow-sm">{{ $confirmedOrdersCount }}</span>
                    </div>

                    <div class="flex flex-col gap-3">
                        @forelse($confirmedOrders as $order)
                            <div class="bg-white dark:bg-gray-900 rounded-[20px] p-5 shadow-[0_4px_15px_rgb(0,0,0,0.02)] border border-white hover:border-blue-500/30 hover:shadow-[0_8px_25px_rgb(0,0,0,0.06)] transition-all">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 mb-1 uppercase tracking-wider">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        <h4 class="font-bold text-base text-gray-900 dark:text-white leading-tight">{{ $order->customer_name }}</h4>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between bg-blue-50/50 rounded-xl p-3 mb-4">
                                    <span class="text-xs font-bold text-gray-500">Amount</span>
                                    <span class="font-black text-blue-600">Rs.{{ number_format($order->total_amount) }}</span>
                                </div>

                                <form method="POST" action="{{ route('orders.ship', $order) }}">
                                    @csrf
                                    <button type="submit" class="w-full bg-blue-50 text-blue-600 font-bold py-2.5 px-4 rounded-xl hover:bg-blue-500 hover:text-white hover:shadow-lg hover:shadow-blue-500/30 active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 text-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        Ship with Pathao
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="rounded-[20px] p-8 text-center text-gray-400 bg-white border border-dashed border-gray-200">
                                <p class="font-bold text-sm">No confirmed orders</p>
                            </div>
                        @endforelse

                        @if($confirmedOrdersCount > 20)
                            <a href="{{ route('orders.index', ['status' => 'confirmed']) }}" class="block text-center text-xs font-bold text-gray-500 hover:text-blue-500 transition-colors py-2 uppercase tracking-wider">
                                View all {{ $confirmedOrdersCount }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Column: Shipped -->
                <div class="w-full md:w-[400px] shrink-0 bg-gray-100/50 dark:bg-gray-800/30 rounded-[32px] p-4 border border-gray-200/60" x-show="!isMobile || activeTab === 'shipped'">
                    <div class="flex items-center justify-between mb-4 px-2">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 bg-green-500 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.6)]"></div>
                            <h3 class="text-base font-black text-gray-900 uppercase tracking-widest">Shipped</h3>
                        </div>
                        <span class="bg-white px-2.5 py-0.5 rounded-full text-xs font-bold text-gray-500 shadow-sm">{{ $shippedOrdersCount }}</span>
                    </div>

                    <div class="flex flex-col gap-3">
                        @forelse($shippedOrders as $order)
                            <div class="bg-white dark:bg-gray-900 rounded-[20px] p-5 shadow-[0_4px_15px_rgb(0,0,0,0.02)] border border-white hover:border-green-500/30 hover:shadow-[0_8px_25px_rgb(0,0,0,0.06)] transition-all">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 mb-1 uppercase tracking-wider">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        <h4 class="font-bold text-base text-gray-900 dark:text-white leading-tight">{{ $order->customer_name }}</h4>
                                    </div>
                                </div>
                                
                                <div class="bg-green-50/50 p-3 rounded-xl border border-green-100/50 flex flex-col items-center justify-center">
                                    <p class="text-[10px] font-black text-green-600/70 uppercase tracking-widest mb-1">Tracking ID</p>
                                    <div class="font-black text-green-700 font-mono tracking-tight flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ $order->pathao_consignment_id }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[20px] p-8 text-center text-gray-400 bg-white border border-dashed border-gray-200">
                                <p class="font-bold text-sm">No shipped orders</p>
                            </div>
                        @endforelse

                        @if($shippedOrdersCount > 20)
                            <a href="{{ route('orders.index', ['status' => 'shipped']) }}" class="block text-center text-xs font-bold text-gray-500 hover:text-green-500 transition-colors py-2 uppercase tracking-wider">
                                View all {{ $shippedOrdersCount }}
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Manual Order Modal -->
    <x-modal name="manual-order-modal" focusable>
        <form method="POST" action="{{ route('orders.store') }}" class="p-8">
            @csrf
            <div class="mb-8">
                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Create Manual Order</h2>
                <p class="text-sm font-medium text-gray-500 mt-1">Enter details for an offline or direct order.</p>
            </div>
            
            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Customer Name</label>
                        <input name="customer_name" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required />
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Phone Number</label>
                        <input name="customer_phone" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Street Address</label>
                        <input name="address" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" required />
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">City</label>
                        <input name="city" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors" />
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Select Product</label>
                    <select name="product_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-bold text-gray-900 transition-colors" required>
                        <option value="" disabled selected>-- Choose a Product --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} (Rs.{{ number_format($product->price) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Quantity</label>
                    <input name="quantity" type="number" min="1" value="1" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-bold text-gray-900 transition-colors" required />
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 transition">Create Order</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
