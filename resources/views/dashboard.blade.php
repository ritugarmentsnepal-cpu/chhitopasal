<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                {{ __('Mission Control') }}
            </h2>
            <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'manual-order-modal')" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-gray-800 transition duration-150 active:scale-95 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden sm:inline">Manual Order</span>
            </button>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F8FAFC] min-h-screen" x-data="{ activeTab: 'pending', isMobile: window.innerWidth < 768 }" x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 768 })">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(isset($lowStockProducts) && $lowStockProducts->count() > 0)
                <div class="mb-6 bg-red-50 text-red-700 px-6 py-4 rounded-2xl shadow-sm border border-red-200 flex flex-col sm:flex-row sm:items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <div class="flex items-center gap-3 font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Low Stock Alert ({{ $lowStockProducts->count() }} items below threshold):</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($lowStockProducts->take(5) as $product)
                            <span class="bg-white text-red-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-red-200">{{ $product->name }} ({{ $product->stock }})</span>
                        @endforeach
                        @if($lowStockProducts->count() > 5)
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-lg">+{{ $lowStockProducts->count() - 5 }} more</span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Analytics Widgets -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col justify-between">
                    <p class="text-sm font-bold text-gray-500 mb-2">Pending Orders</p>
                    <p class="text-3xl font-black text-gray-900">{{ $pendingOrders->count() }}</p>
                </div>
                <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col justify-between">
                    <p class="text-sm font-bold text-gray-500 mb-2">Ready to Ship</p>
                    <p class="text-3xl font-black text-blue-500">{{ $confirmedOrders->count() }}</p>
                </div>
                <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col justify-between">
                    <p class="text-sm font-bold text-gray-500 mb-2">Shipped</p>
                    <p class="text-3xl font-black text-green-500">{{ $shippedOrders->count() }}</p>
                </div>
                <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 flex flex-col justify-between bg-gradient-to-br from-gray-900 to-gray-800 text-white">
                    <p class="text-sm font-bold text-gray-400 mb-2">Pipeline Value</p>
                    <p class="text-3xl font-black text-mango">
                        Rs.{{ number_format($pendingOrders->sum('total_amount') + $confirmedOrders->sum('total_amount') + $shippedOrders->sum('total_amount')) }}
                    </p>
                </div>
            </div>

            <!-- Quick Action Shortcuts -->
            <div class="mb-8 bg-white p-6 rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100">
                <h3 class="text-lg font-black text-gray-900 mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-4">
                    @if(in_array(auth()->user()->role, ['admin', 'manager', 'accountant']))
                        <a href="{{ route('accounting.index') }}" class="flex items-center gap-2 px-5 py-3 bg-green-50 text-green-700 font-bold rounded-xl hover:bg-green-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Financial Dashboard
                        </a>
                        <a href="{{ route('purchases.index') }}" class="flex items-center gap-2 px-5 py-3 bg-purple-50 text-purple-700 font-bold rounded-xl hover:bg-purple-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Record Purchases
                        </a>
                        <a href="{{ route('expenses.index') }}" class="flex items-center gap-2 px-5 py-3 bg-red-50 text-red-700 font-bold rounded-xl hover:bg-red-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Record Expenses
                        </a>
                    @endif
                </div>
            </div>

            <!-- Mobile Tabs -->
            <div class="md:hidden flex gap-2 mb-6 overflow-x-auto pb-2 no-scrollbar">
                <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'bg-mango text-gray-900' : 'bg-white text-gray-500'" class="px-6 py-2.5 rounded-full font-bold whitespace-nowrap shadow-sm border border-gray-100 transition-colors">Pending ({{ $pendingOrders->count() }})</button>
                <button @click="activeTab = 'confirmed'" :class="activeTab === 'confirmed' ? 'bg-blue-500 text-white' : 'bg-white text-gray-500'" class="px-6 py-2.5 rounded-full font-bold whitespace-nowrap shadow-sm border border-gray-100 transition-colors">Confirmed ({{ $confirmedOrders->count() }})</button>
                <button @click="activeTab = 'shipped'" :class="activeTab === 'shipped' ? 'bg-green-500 text-white' : 'bg-white text-gray-500'" class="px-6 py-2.5 rounded-full font-bold whitespace-nowrap shadow-sm border border-gray-100 transition-colors">Shipped ({{ $shippedOrders->count() }})</button>
            </div>

            <!-- Kanban Board (Desktop: Flex Row, Mobile: Single Column based on Tab) -->
            <div class="flex flex-col md:flex-row gap-8 items-start overflow-x-auto pb-8 -mx-4 px-4 sm:mx-0 sm:px-0">
                
                <!-- Column: Pending -->
                <div class="w-full md:w-[400px] shrink-0" x-show="!isMobile || activeTab === 'pending'">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 bg-mango rounded-full animate-pulse shadow-[0_0_10px_#FFD166]"></div>
                            <h3 class="text-xl font-black text-gray-900">Pending</h3>
                        </div>
                        <span class="bg-white px-3 py-1 rounded-full text-sm font-bold text-gray-500 border border-gray-200 shadow-sm">{{ $pendingOrders->count() }}</span>
                    </div>

                    <div class="flex flex-col gap-4">
                        @forelse($pendingOrders as $order)
                            <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 mb-1">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        <h4 class="font-black text-lg text-gray-900 leading-tight">{{ $order->customer_name }}</h4>
                                    </div>
                                    <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2.5 py-1 rounded-lg">{{ $order->source }}</span>
                                </div>
                                
                                <div class="text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded-2xl">
                                    <p class="flex items-center gap-2 mb-1.5"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" /></svg> {{ $order->customer_phone }}</p>
                                    <p class="flex items-center gap-2 line-clamp-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg> {{ $order->address }}, {{ $order->city }}</p>
                                </div>
                                
                                <div class="mb-5">
                                    @foreach($order->orderItems as $item)
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-700">{{ $item->product->name ?? 'Unknown' }} <span class="font-bold text-gray-900">x{{ $item->quantity }}</span></span>
                                        </div>
                                    @endforeach
                                    <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between font-black text-gray-900">
                                        <span>Total</span>
                                        <span class="text-mango">Rs.{{ number_format($order->total_amount) }}</span>
                                    </div>
                                </div>

                                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-order-modal-{{ $order->id }}')" class="w-full bg-white border-2 border-gray-200 text-gray-900 font-bold py-3 px-4 rounded-xl hover:border-mango hover:bg-mango/5 active:scale-95 transition-all duration-200">
                                    Verify Details
                                </button>
                            </div>
                            
                            <!-- Confirm Order Modal inside the loop -->
                            <x-modal name="confirm-order-modal-{{ $order->id }}" focusable>
                                <form method="POST" action="{{ route('orders.status', $order) }}" class="p-8">
                                    @csrf
                                    <input type="hidden" name="status" value="confirmed">
                                    <div class="mb-6">
                                        <h2 class="text-2xl font-black text-gray-900">Verify Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h2>
                                        <p class="text-sm text-gray-500 mt-1">Confirm quantities before sending to Pathao.</p>
                                    </div>
                                    
                                    <div class="space-y-4 mb-8">
                                        @foreach($order->orderItems as $item)
                                            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                                <div>
                                                    <p class="font-bold text-gray-900">{{ $item->product->name ?? 'Unknown Product' }}</p>
                                                    <p class="text-xs text-wildOrchid font-bold mt-1">Rs.{{ number_format($item->price_at_purchase) }} × {{ $item->quantity }}</p>
                                                </div>
                                                <span class="font-black text-gray-900">{{ $item->quantity }} pcs</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex justify-end gap-3">
                                        <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                                        <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 shadow-lg active:scale-95 transition">Confirm Order</button>
                                    </div>
                                </form>
                            </x-modal>
                        @empty
                            <div class="border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center text-gray-400">
                                <p class="font-bold">No pending orders</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Column: Confirmed (Ready to Ship) -->
                <div class="w-full md:w-[400px] shrink-0" x-show="!isMobile || activeTab === 'confirmed'">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>
                            <h3 class="text-xl font-black text-gray-900">Confirmed</h3>
                        </div>
                        <span class="bg-white px-3 py-1 rounded-full text-sm font-bold text-gray-500 border border-gray-200 shadow-sm">{{ $confirmedOrders->count() }}</span>
                    </div>

                    <div class="flex flex-col gap-4">
                        @forelse($confirmedOrders as $order)
                            <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 mb-1">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        <h4 class="font-black text-lg text-gray-900 leading-tight">{{ $order->customer_name }}</h4>
                                    </div>
                                </div>
                                
                                <div class="text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded-2xl border border-gray-100">
                                    <p class="flex justify-between font-bold text-gray-900">
                                        <span>Total Amount</span>
                                        <span class="text-blue-500">Rs.{{ number_format($order->total_amount) }}</span>
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('orders.ship', $order) }}">
                                    @csrf
                                    <button type="submit" class="w-full bg-blue-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-600 active:scale-95 transition-all duration-200 flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Ship with Pathao
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center text-gray-400">
                                <p class="font-bold">No confirmed orders</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Column: Dispatched -->
                <div class="w-full md:w-[400px] shrink-0" x-show="!isMobile || activeTab === 'shipped'">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>
                            <h3 class="text-xl font-black text-gray-900">Shipped</h3>
                        </div>
                        <span class="bg-white px-3 py-1 rounded-full text-sm font-bold text-gray-500 border border-gray-200 shadow-sm">{{ $shippedOrders->count() }}</span>
                    </div>

                    <div class="flex flex-col gap-4">
                        @forelse($shippedOrders as $order)
                            <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 hover:shadow-lg transition-shadow">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-xs font-bold text-gray-400 mb-1">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        <h4 class="font-black text-lg text-gray-900 leading-tight">{{ $order->customer_name }}</h4>
                                    </div>
                                </div>
                                
                                <div class="bg-green-50 p-4 rounded-2xl border border-green-100">
                                    <p class="text-xs font-bold text-green-600 uppercase tracking-wider mb-2">Tracking ID</p>
                                    <div class="flex items-center text-green-800 font-black text-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $order->pathao_consignment_id }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center text-gray-400">
                                <p class="font-bold">No shipped orders</p>
                            </div>
                        @endforelse
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
                <h2 class="text-2xl font-black text-gray-900">Create Manual Order</h2>
                <p class="text-sm text-gray-500 mt-1">Enter details for an offline order.</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="customer_name" class="block text-sm font-bold text-gray-700 mb-2">Customer Name</label>
                    <input id="customer_name" name="customer_name" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors" required />
                </div>
                <div>
                    <label for="customer_phone" class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                    <input id="customer_phone" name="customer_phone" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors" required />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="address" class="block text-sm font-bold text-gray-700 mb-2">Street Address</label>
                    <input id="address" name="address" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors" required />
                </div>
                <div>
                    <label for="city" class="block text-sm font-bold text-gray-700 mb-2">City</label>
                    <input id="city" name="city" type="text" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors" />
                </div>
            </div>

            <div class="mb-5">
                <label for="product_id" class="block text-sm font-bold text-gray-700 mb-2">Select Product</label>
                <select id="product_id" name="product_id" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required>
                    <option value="" disabled selected>-- Choose a Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (Rs.{{ number_format($product->price) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-8">
                <label for="quantity" class="block text-sm font-bold text-gray-700 mb-2">Quantity</label>
                <input id="quantity" name="quantity" type="number" min="1" value="1" class="block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm focus:border-wildOrchid focus:ring focus:ring-wildOrchid/20 py-3 transition-colors font-bold" required />
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-6 py-3 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 active:scale-95 transition">Create Order</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
