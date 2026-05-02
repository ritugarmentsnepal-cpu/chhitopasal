<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                {{ __('Order Management') }}
            </h2>
            @if($status === 'pending')
                <div class="flex gap-2">
                    <button x-data="" @click="window.dispatchEvent(new CustomEvent('open-bulk-modal'))" class="bg-white border border-gray-200 text-gray-700 font-bold py-2.5 px-5 rounded-xl shadow-sm hover:bg-gray-50 transition duration-150 active:scale-95 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        <span class="hidden sm:inline">Bulk Upload</span>
                    </button>
                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-order-modal')" class="bg-gray-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-gray-800 transition duration-150 active:scale-95 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span class="hidden sm:inline">Manual Order</span>
                    </button>
                </div>
            @elseif($status === 'shipped')
                <div class="flex gap-2">
                    <form action="{{ route('orders.masterSyncPathao') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-mango text-gray-900 font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-[#FFC033] transition duration-150 active:scale-95 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span class="hidden sm:inline">Master Sync Status</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-[#F8FAFC] min-h-screen" x-data="orderManager()">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 bg-red-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Tabs Navigation -->
            <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex overflow-x-auto no-scrollbar mb-6">
                @php
                    $tabs = [
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'return_delivered' => 'Returned',
                        'failed' => 'Failed',
                        'rejected' => 'Rejected'
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}" class="px-6 py-2.5 rounded-xl font-bold whitespace-nowrap transition-all flex-1 text-center {{ $status === $key ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if(in_array($status, ['shipped', 'delivered', 'return_delivered', 'failed', 'rejected']))
            <!-- Pathao Delivery Status Sub-Filters -->
            <div class="mb-4 flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap mr-1 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    Pathao:
                </span>
                @php
                    $allPathaoFilters = [
                        '' => ['label' => 'All', 'dot' => 'bg-gray-400'],
                        'awaiting_pickup' => ['label' => 'Awaiting Pickup', 'dot' => 'bg-yellow-400'],
                        'Picked' => ['label' => 'Picked Up', 'dot' => 'bg-blue-400'],
                        'In Transit' => ['label' => 'In Transit', 'dot' => 'bg-indigo-500'],
                        'At Hub' => ['label' => 'At Hub', 'dot' => 'bg-purple-500'],
                        'Out for Delivery' => ['label' => 'Out for Delivery', 'dot' => 'bg-orange-500'],
                        'Delivered' => ['label' => 'Delivered', 'dot' => 'bg-green-500'],
                        'Return' => ['label' => 'Returned', 'dot' => 'bg-red-500'],
                        'Cancel' => ['label' => 'Cancelled', 'dot' => 'bg-gray-500'],
                    ];

                    $pathaoFilters = ['' => $allPathaoFilters['']];
                    
                    if ($status === 'shipped') {
                        $pathaoFilters += [
                            'awaiting_pickup' => $allPathaoFilters['awaiting_pickup'],
                            'Picked' => $allPathaoFilters['Picked'],
                            'In Transit' => $allPathaoFilters['In Transit'],
                            'At Hub' => $allPathaoFilters['At Hub'],
                            'Out for Delivery' => $allPathaoFilters['Out for Delivery']
                        ];
                    } elseif ($status === 'delivered') {
                        $pathaoFilters += ['Delivered' => $allPathaoFilters['Delivered']];
                    } elseif ($status === 'return_delivered') {
                        $pathaoFilters += ['Return' => $allPathaoFilters['Return']];
                    } elseif (in_array($status, ['failed', 'rejected'])) {
                        $pathaoFilters += [
                            'Cancel' => $allPathaoFilters['Cancel'],
                            'Return' => $allPathaoFilters['Return']
                        ];
                    }

                    $currentPathaoFilter = request('pathao_filter', '');
                @endphp
                @foreach($pathaoFilters as $filterKey => $filterData)
                    <a href="{{ request()->fullUrlWithQuery(['pathao_filter' => $filterKey ?: null]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition-all border
                              {{ $currentPathaoFilter === $filterKey ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">
                        <span class="w-2 h-2 rounded-full {{ $filterData['dot'] }} {{ $currentPathaoFilter === $filterKey ? 'ring-2 ring-white/40' : '' }}"></span>
                        {{ $filterData['label'] }}
                    </a>
                @endforeach
            </div>
            @endif

            <!-- Filter Bar -->
            <form method="GET" action="{{ route('orders.index') }}" class="mb-6 flex gap-4 items-center bg-white p-3 rounded-2xl shadow-sm border border-gray-100">
                <input type="hidden" name="status" value="{{ $status }}">
                @if(request('pathao_filter'))
                    <input type="hidden" name="pathao_filter" value="{{ request('pathao_filter') }}">
                @endif
                
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Order ID, Name, or Phone..." class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-mango focus:ring-mango transition-colors font-medium">
                </div>

                <div class="w-48 relative">
                    <select name="date_filter" onchange="this.form.submit()" class="block w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-mango focus:ring-mango transition-colors font-medium appearance-none">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('date_filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>

                <button type="submit" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold shadow-sm hover:bg-gray-800 transition active:scale-95 whitespace-nowrap">
                    Search
                </button>
                @if(request('search') || request('date_filter') || request('pathao_filter'))
                    <a href="{{ route('orders.index', ['status' => $status]) }}" class="text-gray-500 hover:text-red-500 font-bold px-4 transition">Clear</a>
                @endif
            </form>

            <!-- Orders Table -->
            <div class="bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 overflow-hidden">
                <div x-show="selectedOrders.length > 0" x-cloak class="border-b px-6 py-3 flex items-center justify-between transition-all
                    {{ $status === 'pending' ? 'bg-red-50 border-red-100' : ($status === 'confirmed' ? 'bg-emerald-50 border-emerald-100' : 'bg-blue-50 border-blue-100') }}">
                    <span class="text-sm font-bold {{ $status === 'pending' ? 'text-red-900' : ($status === 'confirmed' ? 'text-emerald-900' : 'text-blue-900') }}"><span x-text="selectedOrders.length"></span> orders selected</span>
                    <div class="flex items-center gap-2">
                        @if($status === 'pending')
                            <button type="button" @click="bulkDeleteOrders()" :disabled="bulkProcessing" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-sm flex items-center gap-2 transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="!bulkProcessing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <svg x-show="bulkProcessing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-text="bulkProcessing ? 'Deleting...' : 'Delete Selected'"></span>
                            </button>
                        @elseif($status === 'confirmed')
                            <button type="button" @click="bulkShipOrders()" :disabled="bulkProcessing" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-sm flex items-center gap-2 transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="!bulkProcessing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                                <svg x-show="bulkProcessing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-text="bulkProcessing ? 'Shipping...' : 'Ship All via Pathao'"></span>
                            </button>
                        @else
                            <form action="{{ route('orders.bulkPrint') }}" method="POST" target="_blank" class="inline">
                                @csrf
                                <input type="hidden" name="order_ids" :value="JSON.stringify(selectedOrders)">
                                <input type="hidden" name="print_mode" value="a4">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-sm flex items-center gap-2 transition active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                    A4 Label
                                </button>
                            </form>
                            <form action="{{ route('orders.bulkPrint') }}" method="POST" target="_blank" class="inline">
                                @csrf
                                <input type="hidden" name="order_ids" :value="JSON.stringify(selectedOrders)">
                                <input type="hidden" name="print_mode" value="thermal">
                                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-sm flex items-center gap-2 transition active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Thermal Printer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider font-bold">
                                <th class="p-4 border-b border-gray-100 rounded-tl-3xl w-10">
                                    <input type="checkbox" class="rounded border-gray-300 text-mango focus:ring-mango" 
                                           @change="$event.target.checked ? selectAll() : deselectAll()"
                                           :checked="selectedOrders.length === {{ count($orders) }} && {{ count($orders) }} > 0">
                                </th>
                                <th class="p-4 border-b border-gray-100">ID & Date</th>
                                <th class="p-4 border-b border-gray-100">Customer</th>
                                <th class="p-4 border-b border-gray-100">Location</th>
                                @if($status === 'shipped')
                                    <th class="p-4 border-b border-gray-100">Pathao Status</th>
                                @endif
                                <th class="p-4 border-b border-gray-100">Items & Total</th>
                                <th class="p-4 border-b border-gray-100 text-right rounded-tr-3xl">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="p-4 align-top">
                                        <input type="checkbox" class="rounded border-gray-300 text-mango focus:ring-mango order-checkbox" 
                                               value="{{ $order->id }}"
                                               x-model="selectedOrders">
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="font-black text-gray-900">#{{ $order->id }}</div>
                                        <div class="text-xs text-gray-500 font-medium">{{ $order->created_at->format('M d, g:i A') }}</div>
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->source === 'web' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                            {{ $order->source }}
                                        </span>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="font-bold text-gray-900">{{ $order->customer_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $order->customer_phone }}</div>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="text-sm text-gray-900 font-medium line-clamp-1">{{ $order->address }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->city ?? 'N/A' }}</div>
                                        @if($order->pathao_consignment_id)
                                            <div class="mt-1 text-xs font-bold text-wildOrchid flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ $order->pathao_consignment_id }}
                                            </div>
                                        @endif
                                    </td>
                                    @if($status === 'shipped')
                                    <td class="p-4 align-top">
                                        @php
                                            $ps = strtolower($order->pathao_status ?? '');
                                            $badgeClass = match(true) {
                                                str_contains($ps, 'delivered') => 'bg-green-50 text-green-700 border-green-200',
                                                str_contains($ps, 'transit') || str_contains($ps, 'in transit') => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                str_contains($ps, 'picked') || str_contains($ps, 'pickup') => 'bg-blue-50 text-blue-700 border-blue-200',
                                                str_contains($ps, 'hub') || str_contains($ps, 'sorting') => 'bg-purple-50 text-purple-700 border-purple-200',
                                                str_contains($ps, 'out for') => 'bg-orange-50 text-orange-700 border-orange-200',
                                                str_contains($ps, 'return') => 'bg-red-50 text-red-700 border-red-200',
                                                str_contains($ps, 'cancel') => 'bg-gray-100 text-gray-600 border-gray-200',
                                                default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $badgeClass }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                            {{ $order->pathao_status ?? 'Awaiting Pickup' }}
                                        </span>
                                        @if($order->pathao_status_updated_at)
                                            <div class="text-[10px] text-gray-400 mt-1">{{ \Carbon\Carbon::parse($order->pathao_status_updated_at)->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="p-4 align-top">
                                        <div x-data="{ editingAmount: false, newAmount: {{ $order->total_amount }} }" class="mb-1">
                                            <div x-show="!editingAmount" class="flex items-center gap-2">
                                                <span class="text-sm text-gray-900 font-bold">Rs.{{ number_format($order->total_amount) }}</span>
                                                <button @click="editingAmount = true" class="text-gray-400 hover:text-mango">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </button>
                                            </div>
                                            <form x-show="editingAmount" action="{{ route('orders.updateAmount', $order) }}" method="POST" class="flex items-center gap-2" x-cloak>
                                                @csrf @method('PATCH')
                                                <input type="number" step="0.01" name="total_amount" x-model="newAmount" class="w-20 rounded border-gray-200 py-0.5 px-2 text-xs font-bold">
                                                <button type="submit" class="text-green-500 hover:text-green-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></button>
                                                <button type="button" @click="editingAmount = false; newAmount = {{ $order->total_amount }}" class="text-red-500 hover:text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                            </form>
                                        </div>
                                        <ul class="text-xs text-gray-500 space-y-0.5">
                                            @foreach($order->orderItems as $item)
                                                <li>{{ $item->quantity }}x {{ $item->product ? $item->product->name : 'Deleted Product' }}</li>
                                            @endforeach
                                        </ul>
                                        @if($order->remarks)
                                            <div class="mt-2 text-xs p-2 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg whitespace-pre-wrap break-words">
                                                <strong>Remarks:</strong><br>{{ $order->remarks }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-4 align-top text-right w-[320px]">
                                        <div class="flex flex-col gap-2 w-full ml-auto">
                                            @if(in_array($status, ['pending', 'confirmed']))
                                                <button @click="openEditModal({{ $order }})" class="inline-flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-200 active:scale-95 transition-all w-full justify-center">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                    {{ $status === 'pending' ? 'Review & Process' : 'Edit Details' }}
                                                </button>
                                            @endif

                                            @if($status === 'pending' || $status === 'confirmed')
                                                <form action="{{ route('orders.status', $order) }}" method="POST" class="w-full">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-2 px-2 rounded-lg text-xs transition flex items-center justify-center">
                                                        Reject Order
                                                    </button>
                                                </form>
                                            @endif

                                            @if($status === 'confirmed')
                                                <form action="{{ route('orders.ship', $order) }}" method="POST" class="w-full">
                                                    @csrf
                                                    <button type="submit" name="print_type" value="both" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-2 rounded-lg transition text-xs shadow-sm text-center">Ship via Pathao</button>
                                                </form>
                                            @elseif($status === 'shipped')
                                                <button @click="openTrackingModal({{ $order->id }})" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-2 rounded-lg transition text-xs shadow-sm text-center flex items-center justify-center gap-1.5 mb-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                                                    Track Order
                                                </button>
                                                <a href="{{ route('orders.printLabel', ['order' => $order, 'type' => 'both']) }}" target="_blank" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-2 px-2 rounded-lg transition text-xs shadow-sm text-center block mb-1">Print Label & Invoice</a>
                                            @elseif(in_array($status, ['delivered', 'return_delivered']))
                                                <a href="{{ route('orders.printLabel', ['order' => $order, 'type' => 'both']) }}" target="_blank" class="w-full bg-gray-700 hover:bg-gray-800 text-white font-bold py-2 px-2 rounded-lg transition text-xs shadow-sm text-center block mb-1">Print Both</a>
                                            @endif
                                            
                                            @if($order->status === 'return_delivered' && !$order->return_verified_at)
                                                <form action="{{ route('orders.verifyReturn', $order) }}" method="POST" class="w-full mt-1">
                                                    @csrf
                                                    <button type="submit" class="w-full bg-orange-100 text-orange-700 hover:bg-orange-200 font-bold py-2 px-2 rounded-lg text-xs transition flex items-center justify-center">
                                                        Verify Return
                                                    </button>
                                                </form>
                                            @elseif($order->return_verified_at)
                                                <div class="text-xs font-bold text-green-600 flex items-center justify-center gap-1 w-full bg-green-50 py-2 rounded-lg mt-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Return Verified
                                                </div>
                                            @endif

                                            @if($order->payment_status !== 'paid' && in_array($order->status, ['pending', 'confirmed', 'shipped', 'delivered']))
                                                <button @click="openPaymentModal({{ $order }})" class="w-full flex items-center justify-center text-center bg-green-50 text-green-700 hover:bg-green-100 font-bold py-1.5 px-2 rounded-lg text-xs transition h-full">
                                                    Payment
                                                </button>
                                            @elseif($order->payment_status === 'paid')
                                                <div class="text-xs font-bold text-green-600 flex items-center justify-center gap-1 w-full bg-green-50 py-1.5 rounded-lg h-full">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Paid
                                                </div>
                                            @elseif($order->payment_status === 'partial')
                                                <div class="text-xs font-bold text-orange-500 flex items-center justify-center gap-1 w-full bg-orange-50 py-1.5 rounded-lg h-full">
                                                    Partial: Rs.{{ $order->paid_amount }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $status === 'shipped' ? 7 : 6 }}" class="p-12 text-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <p class="font-bold text-lg">No {{ $status }} orders found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-3xl">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Smart Confirmation Popup Removed, logic merged into Edit Popup -->

        <!-- Bulk Upload Spreadsheet Modal -->
        <div x-show="bulkModalOpen" x-cloak class="fixed inset-0 z-50 overflow-auto" aria-labelledby="bulk-modal" role="dialog" aria-modal="true">
            <div class="flex items-start justify-center min-h-screen p-2 sm:p-4">
                <div x-show="bulkModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity" @click="closeBulkModal()"></div>

                <div x-show="bulkModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-[1.5rem] shadow-2xl transform transition-all w-full max-w-[95vw] max-h-[90vh] flex flex-col z-10 my-4" style="overflow: hidden;">
                    
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50/40 flex justify-between items-center flex-shrink-0">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" /></svg>
                                Bulk Order Entry
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Fill in customer details like a spreadsheet. All fields are editable.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1.5 rounded-lg">
                                <span x-text="bulkRows.length"></span> rows
                            </span>
                            <button type="button" @click="closeBulkModal()" class="text-gray-400 hover:text-gray-900 transition p-1"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        </div>
                    </div>

                    <!-- Spreadsheet Table -->
                    <div class="flex-1 min-h-0" style="overflow: auto;">
                        <table class="w-full text-sm border-collapse" id="bulk-spreadsheet" style="min-width: 1000px;">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-gray-800 text-white text-xs uppercase tracking-wider font-bold">
                                    <th class="px-2 py-3 text-center w-12 border-r border-gray-700">#</th>
                                    <th class="px-2 py-3 text-left min-w-[160px] border-r border-gray-700">Customer Name <span class="text-red-400">*</span></th>
                                    <th class="px-2 py-3 text-left min-w-[130px] border-r border-gray-700">Phone <span class="text-red-400">*</span></th>
                                    <th class="px-2 py-3 text-left min-w-[200px] border-r border-gray-700">Address <span class="text-red-400">*</span></th>
                                    <th class="px-2 py-3 text-left min-w-[120px] border-r border-gray-700">City</th>
                                    <th class="px-2 py-3 text-left min-w-[220px] border-r border-gray-700">Product <span class="text-red-400">*</span></th>
                                    <th class="px-2 py-3 text-center min-w-[70px] border-r border-gray-700">Qty</th>
                                    <th class="px-2 py-3 text-center min-w-[110px] border-r border-gray-700">Amount (Rs.)</th>
                                    <th class="px-2 py-3 text-center w-14"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in bulkRows" :key="idx">
                                    <tr class="border-b border-gray-100 hover:bg-blue-50/30 transition-colors group" :class="row._error ? 'bg-red-50' : ''">
                                        <td class="px-2 py-1 text-center text-xs font-bold text-gray-400 border-r border-gray-100 bg-gray-50" x-text="idx + 1"></td>
                                        <td class="px-1 py-1 border-r border-gray-100">
                                            <input type="text" x-model="row.customer_name" placeholder="Full Name" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition">
                                        </td>
                                        <td class="px-1 py-1 border-r border-gray-100">
                                            <input type="text" x-model="row.customer_phone" placeholder="98XXXXXXXX" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition">
                                        </td>
                                        <td class="px-1 py-1 border-r border-gray-100">
                                            <input type="text" x-model="row.address" placeholder="Street address" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition">
                                        </td>
                                        <td class="px-1 py-1 border-r border-gray-100">
                                            <input type="text" x-model="row.city" placeholder="City" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-medium placeholder:text-gray-300 transition">
                                        </td>
                                        <td class="px-1 py-1 border-r border-gray-100">
                                            <select x-model="row.product_selection" @change="onBulkProductChange(idx)" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-1 py-1.5 text-sm font-medium transition cursor-pointer">
                                                <option value="">-- Select Product --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}:1:{{ $product->price }}">{{ $product->name }} (Rs.{{ number_format($product->price) }})</option>
                                                    @if($product->bundles)
                                                        @foreach($product->bundles as $bundle)
                                                            <option value="{{ $product->id }}:{{ $bundle['qty'] }}:{{ $bundle['price'] }}">{{ $product->name }} ({{ $bundle['qty'] }}-Pack — Rs.{{ number_format($bundle['price']) }})</option>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-1 py-1 border-r border-gray-100">
                                            <input type="number" x-model.number="row.quantity" min="1" @input="onBulkQtyChange(idx)" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-bold text-center placeholder:text-gray-300 transition">
                                        </td>
                                        <td class="px-1 py-1 border-r border-gray-100">
                                            <input type="number" x-model.number="row.amount" step="0.01" min="0" class="w-full border-0 bg-transparent focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-2 py-1.5 text-sm font-bold text-center placeholder:text-gray-300 transition">
                                        </td>
                                        <td class="px-1 py-1 text-center">
                                            <button type="button" @click="removeBulkRow(idx)" class="p-1 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition opacity-0 group-hover:opacity-100" :class="bulkRows.length <= 1 ? 'invisible' : ''">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex flex-wrap items-center justify-between gap-3 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <button type="button" @click="addBulkRow()" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-50 transition active:scale-95 flex items-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                Add Row
                            </button>
                            <button type="button" @click="addBulkRows(5)" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-50 transition active:scale-95 shadow-sm">
                                +5 Rows
                            </button>
                            <button type="button" @click="addBulkRows(10)" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-sm hover:bg-gray-50 transition active:scale-95 shadow-sm">
                                +10 Rows
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-sm font-bold text-gray-600 mr-2">
                                Total: <span class="text-lg text-gray-900" x-text="'Rs. ' + bulkGrandTotal().toLocaleString()"></span>
                                <span class="text-xs text-gray-400 ml-1">(<span x-text="bulkFilledRows()"></span> valid orders)</span>
                            </div>
                            <button type="button" @click="closeBulkModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                            <button type="button" @click="submitBulkOrders()" :disabled="bulkSubmitting" class="px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 transition active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="bulkSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-text="bulkSubmitting ? 'Creating...' : 'Create All Orders'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Single Order Modal -->
        <!-- Just basic placeholder to not break the UI flow -->
        <x-modal name="add-order-modal" focusable>
            <div class="p-8">
                <h2 class="text-2xl font-black text-gray-900">Add Manual Order</h2>
                <p class="text-sm text-gray-500 mt-1">Use the bulk uploader for multiple orders.</p>
                <form method="POST" action="{{ route('orders.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <!-- Normal form fields... omitted for brevity as bulk upload is preferred -->
                    <input type="text" name="customer_name" placeholder="Name" class="w-full rounded-xl border-gray-200" required>
                    <input type="text" name="customer_phone" placeholder="Phone" class="w-full rounded-xl border-gray-200" required>
                    <input type="text" name="address" placeholder="Address" class="w-full rounded-xl border-gray-200" required>
                    <input type="text" name="city" placeholder="City (Optional)" class="w-full rounded-xl border-gray-200">
                    <select name="product_id" class="w-full rounded-xl border-gray-200" required>
                        @foreach($products as $p) <option value="{{ $p->id }}">{{ $p->name }}</option> @endforeach
                    </select>
                    <input type="number" name="quantity" value="1" min="1" class="w-full rounded-xl border-gray-200" required>
                    
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 bg-gray-100 rounded-lg">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg">Save</button>
                    </div>
                </form>
            </div>
        </x-modal>

        <!-- Full Edit Order Modal -->
        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeEditModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                    
                    <form :action="`{{ url('orders') }}/${selectedEditOrder?.id}/full-update`" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h3 class="text-xl font-black text-gray-900">Edit Order #<span x-text="selectedEditOrder?.id"></span></h3>
                            <button type="button" @click="closeEditModal()" class="text-gray-400 hover:text-gray-900"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        </div>

                        <div class="p-6 space-y-6 max-h-[65vh] overflow-y-auto">
                            <!-- Customer Details -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Customer Name</label>
                                    <input name="customer_name" x-model="editFormData.customer_name" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 focus:ring-mango" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                                    <input name="customer_phone" x-model="editFormData.customer_phone" type="text" class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 focus:ring-mango" required>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                                <h4 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                                    Delivery Details
                                </h4>
                                <div class="mb-3">
                                    <div>
                                        <label class="block text-xs font-bold text-blue-800 mb-1">Street Address</label>
                                        <input name="address" x-model="editFormData.address" type="text" class="w-full rounded-xl border-blue-200 bg-white py-2 focus:ring-blue-500" required>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-blue-800 mb-1">Pathao City</label>
                                        <select name="pathao_city_id" x-model="editFormData.pathao_city_id" @change="fetchEditZones()" class="w-full rounded-xl border-blue-200 bg-white py-2 text-sm focus:ring-blue-500" :required="selectedEditOrder?.status === 'pending'">
                                            <option value="">Select City</option>
                                            <template x-for="city in cities" :key="city.city_id">
                                                <option :value="city.city_id" x-text="city.city_name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-blue-800 mb-1">Pathao Zone</label>
                                        <select name="pathao_zone_id" x-model="editFormData.pathao_zone_id" class="w-full rounded-xl border-blue-200 bg-white py-2 text-sm focus:ring-blue-500" :disabled="!editZones.length" :required="selectedEditOrder?.status === 'pending'">
                                            <option value="">Select Zone</option>
                                            <template x-for="zone in editZones" :key="zone.zone_id">
                                                <option :value="zone.zone_id" x-text="zone.zone_name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            @if(auth()->user()->role === 'admin')
                            <div class="bg-red-50 p-4 rounded-2xl border border-red-100">
                                <label class="block text-sm font-bold text-red-900 mb-1">Admin Only: Override Status</label>
                                <select name="status" x-model="editFormData.status" class="w-full rounded-xl border-red-200 bg-white py-2 focus:ring-red-500 font-bold text-red-700">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="return_delivered">Returned</option>
                                    <option value="failed">Failed</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            @endif

                            <!-- Order Items -->
                            <div>
                                <h4 class="font-bold text-gray-900 mb-3 border-b border-gray-100 pb-2">Order Items & Pricing</h4>
                                <template x-if="selectedEditOrder">
                                    <div class="space-y-3">
                                        <template x-for="(item, index) in editFormData.items" :key="index">
                                            <div class="flex items-start gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100 relative">
                                                <div class="flex-1">
                                                    <label class="block text-xs font-bold text-gray-500 mb-1">Product</label>
                                                    <select :name="`items[${index}][_selection]`" x-model="item.selection" @change="onProductChange(index)" class="w-full rounded-lg border-gray-200 py-1.5 px-2 font-bold focus:ring-mango text-sm" required>
                                                        <option value="" disabled>Select Product</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}:1">{{ $product->name }}</option>
                                                            @if($product->bundles)
                                                                @foreach($product->bundles as $bundle)
                                                                    <option value="{{ $product->id }}:{{ $bundle['qty'] }}">{{ $product->name }} ({{ $bundle['qty'] }}-Pack — Rs.{{ number_format($bundle['price']) }})</option>
                                                                @endforeach
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                                                    <input type="hidden" :name="`items[${index}][id]`" :value="item.id" x-show="item.id">
                                                    <input type="hidden" :name="`items[${index}][color]`" :value="item.color || ''">
                                                    <input type="hidden" :name="`items[${index}][size]`" :value="item.size || ''">
                                                    <!-- Variant badges -->
                                                    <div x-show="item.color || item.size" class="flex gap-1.5 mt-1.5 flex-wrap">
                                                        <span x-show="item.color" class="bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-purple-100" x-text="item.color"></span>
                                                        <span x-show="item.size" class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-blue-100" x-text="'Size: ' + item.size"></span>
                                                    </div>
                                                </div>
                                                <div class="w-20">
                                                    <label class="block text-xs font-bold text-gray-500 mb-1">Qty</label>
                                                    <input :name="`items[${index}][quantity]`" x-model="item.quantity" @input="onQuantityChange(index)" type="number" min="1" class="w-full rounded-lg border-gray-200 py-1.5 px-2 text-center font-bold focus:ring-mango text-sm" required>
                                                </div>
                                                <div class="w-28">
                                                    <label class="block text-xs font-bold text-gray-500 mb-1">Total (Rs.)</label>
                                                    <input :name="`items[${index}][total_price]`" x-model="item.total_price" type="number" step="0.01" min="0" class="w-full rounded-lg border-gray-200 py-1.5 px-2 text-center font-bold focus:ring-mango text-sm" required>
                                                </div>
                                                <button type="button" @click="removeProduct(index)" class="mt-5 p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition" title="Remove Product">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </template>
                                        <button type="button" @click="addProduct()" class="w-full mt-3 py-2 border-2 border-dashed border-gray-200 text-gray-500 font-bold rounded-xl hover:border-mango hover:text-mango transition flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                            Add Product
                                        </button>
                                    </div>
                                </template>
                                </div>
                                
                                <!-- Delivery Charge & Total Override -->
                                <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="w-1/2">
                                            <label class="block text-sm font-bold text-gray-700 mb-1">Delivery Charge (Rs.)</label>
                                            <input name="delivery_charge" x-model.number="editFormData.delivery_charge" type="number" min="0" step="0.01" class="w-full rounded-xl border-gray-200 bg-white py-2 focus:ring-mango font-bold text-gray-900">
                                            <p class="text-xs text-gray-500 mt-1">Can be modified or waived.</p>
                                        </div>
                                        <div class="w-1/2 text-right">
                                            <label class="block text-sm font-bold text-gray-500 mb-1">Grand Total</label>
                                            <div class="text-2xl font-black text-gray-900">Rs.<span x-text="calculateEditGrandTotal()"></span></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                                    <label class="block text-sm font-bold text-yellow-800 mb-1">Internal Remarks</label>
                                    <textarea name="remarks" x-model="editFormData.remarks" rows="2" class="w-full rounded-xl border-yellow-300 bg-white py-2 focus:ring-yellow-500 placeholder-yellow-400 text-sm" placeholder="Add any special notes or remarks here..."></textarea>
                                </div>
                            </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="closeEditModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                            
                            <template x-if="selectedEditOrder?.status === 'pending'">
                                <button type="submit" name="confirm_order" value="1" class="px-5 py-2.5 bg-mango text-gray-900 font-bold rounded-xl shadow-lg hover:bg-[#FFC033] transition active:scale-95">Save & Confirm Order</button>
                            </template>
                            
                            <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white font-bold rounded-xl shadow-lg hover:bg-gray-800 transition active:scale-95" x-text="selectedEditOrder?.status === 'pending' ? 'Save Draft' : 'Save Changes'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Pathao Tracking Detail Modal -->
        <div x-show="trackingModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="tracking-modal" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="trackingModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeTrackingModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="trackingModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">

                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                                Order #<span x-text="trackingData?.order?.id"></span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Last synced: <span x-text="trackingData?.status_updated_at || 'Never'" class="font-bold"></span></p>
                        </div>
                        <button type="button" @click="closeTrackingModal()" class="text-gray-400 hover:text-gray-900 transition"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>

                    <!-- Loading state -->
                    <div x-show="trackingLoading" class="p-12 text-center">
                        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <p class="font-bold text-gray-500">Fetching live status from Pathao...</p>
                    </div>

                    <!-- Content -->
                    <div x-show="!trackingLoading && trackingData" class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

                        <!-- Status Timeline -->
                        <div class="bg-gradient-to-r from-gray-50 to-indigo-50/30 p-5 rounded-2xl border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-black text-gray-900 text-sm">Delivery Progress</h4>
                                <span class="px-3 py-1 rounded-full text-xs font-bold" :class="getStatusBadgeClass(trackingData?.pathao?.status)" x-text="trackingData?.pathao?.status || 'Unknown'"></span>
                            </div>
                            <div class="flex items-center gap-0">
                                <template x-for="(step, idx) in trackingSteps" :key="idx">
                                    <div class="flex items-center" :class="idx < trackingSteps.length - 1 ? 'flex-1' : ''">
                                        <div class="flex flex-col items-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black transition-all duration-300"
                                                 :class="getStepIndex() >= idx ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-gray-200 text-gray-500'">
                                                <span x-text="idx + 1"></span>
                                            </div>
                                            <span class="text-[10px] font-bold mt-1.5 text-center leading-tight w-16" :class="getStepIndex() >= idx ? 'text-indigo-700' : 'text-gray-400'" x-text="step"></span>
                                        </div>
                                        <div x-show="idx < trackingSteps.length - 1" class="h-0.5 flex-1 mx-1 mt-[-16px] rounded-full transition-all duration-300" :class="getStepIndex() > idx ? 'bg-indigo-600' : 'bg-gray-200'"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Info Cards Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Shipment Info -->
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    Shipment Info
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between"><span class="text-gray-500">Tracking ID</span><span class="font-bold text-gray-900 text-xs" x-text="trackingData?.order?.pathao_consignment_id"></span></div>
                                    <div class="flex justify-between"><span class="text-gray-500">Shipped</span><span class="font-bold text-gray-900" x-text="trackingData?.order?.shipped_date"></span></div>
                                    <div class="flex justify-between"><span class="text-gray-500">Items</span><span class="font-bold text-gray-900" x-text="trackingData?.order?.item_count"></span></div>
                                    <div class="flex justify-between"><span class="text-gray-500">Weight</span><span class="font-bold text-gray-900" x-text="(trackingData?.order?.weight_kg || 0).toFixed(1) + ' kg'"></span></div>
                                </div>
                            </div>
                            <!-- Customer Info -->
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    Customer
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between"><span class="text-gray-500">Name</span><span class="font-bold text-gray-900" x-text="trackingData?.order?.customer_name"></span></div>
                                    <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="font-bold text-gray-900" x-text="trackingData?.order?.customer_phone"></span></div>
                                    <div><span class="text-gray-500 text-xs">Address</span><div class="font-bold text-gray-900 text-xs mt-0.5" x-text="(trackingData?.order?.address || '') + (trackingData?.order?.city ? ', ' + trackingData.order.city : '')"></div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider">Order Items</h4>
                            </div>
                            <table class="w-full text-sm">
                                <thead><tr class="text-xs text-gray-500 font-bold uppercase"><th class="px-4 py-2 text-left">Product</th><th class="px-4 py-2 text-center">Qty</th><th class="px-4 py-2 text-right">Amount</th></tr></thead>
                                <tbody class="divide-y divide-gray-50">
                                    <template x-for="item in trackingData?.order?.items || []" :key="item.name">
                                        <tr>
                                            <td class="px-4 py-2.5 font-bold text-gray-900" x-text="item.name"></td>
                                            <td class="px-4 py-2.5 text-center text-gray-600" x-text="item.quantity"></td>
                                            <td class="px-4 py-2.5 text-right font-bold text-gray-900" x-text="'Rs. ' + Number(item.total).toLocaleString()"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial Summary -->
                        <div class="bg-gradient-to-r from-gray-900 to-gray-800 p-5 rounded-2xl text-white">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Subtotal</p>
                                    <p class="text-lg font-black mt-0.5" x-text="'Rs. ' + Number(trackingData?.order?.total_amount || 0).toLocaleString()"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Delivery</p>
                                    <p class="text-lg font-black mt-0.5" x-text="'Rs. ' + Number(trackingData?.order?.delivery_charge || 0).toLocaleString()"></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Advance Paid</p>
                                    <p class="text-lg font-black mt-0.5 text-green-400" x-text="'Rs. ' + Number(trackingData?.order?.paid_amount || 0).toLocaleString()"></p>
                                </div>
                                <div class="bg-white/10 rounded-xl p-2">
                                    <p class="text-[10px] text-yellow-300 uppercase font-bold tracking-wider">COD Collect</p>
                                    <p class="text-lg font-black mt-0.5 text-yellow-300" x-text="'Rs. ' + (Number(trackingData?.order?.total_amount || 0) - Number(trackingData?.order?.paid_amount || 0)).toLocaleString()"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Rider Info -->
                        <div x-show="trackingData?.pathao?.rider_name" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                            <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Delivery Rider
                            </h4>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center border border-orange-100">
                                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-gray-900 text-sm" x-text="trackingData?.pathao?.rider_name"></div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-sm text-gray-500" x-text="trackingData?.pathao?.rider_phone"></span>
                                        <a x-show="trackingData?.pathao?.rider_phone" :href="'tel:' + trackingData?.pathao?.rider_phone" class="inline-flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 px-2.5 py-1 rounded-lg border border-green-100 hover:bg-green-100 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                            Call
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Comments & Remarks -->
                        <div x-show="trackingData?.pathao?.comments || trackingData?.pathao?.failed_reason" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                                <h4 class="font-black text-gray-900 text-xs uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                                    Delivery Comments
                                </h4>
                            </div>
                            <div class="p-4 space-y-3">
                                <div x-show="trackingData?.pathao?.comments">
                                    <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Pathao Remarks</div>
                                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-sm text-blue-900 font-medium" x-text="trackingData?.pathao?.comments"></div>
                                </div>
                                <div x-show="trackingData?.pathao?.failed_reason">
                                    <div class="text-[10px] text-red-400 uppercase font-bold tracking-wider mb-1">Failed Delivery Reason</div>
                                    <div class="bg-red-50 border border-red-100 rounded-xl p-3 text-sm text-red-800 font-medium flex items-start gap-2">
                                        <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        <span x-text="trackingData?.pathao?.failed_reason"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div x-show="!trackingLoading" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-2 justify-between items-center">
                        <div class="flex gap-2">
                            <button @click="copyTrackingId()" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-50 transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                Copy Tracking ID
                            </button>
                            <a x-show="trackingData?.order?.id" :href="`{{ url('orders') }}/${trackingData?.order?.id}/print-label?type=both`" target="_blank" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-50 transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                Print Label
                            </a>
                        </div>
                        <div class="flex gap-2">
                            <button @click="refreshTrackingStatus()" class="px-4 py-2 bg-indigo-600 text-white font-bold rounded-xl text-xs shadow-sm hover:bg-indigo-700 transition active:scale-95 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" :class="trackingLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                Sync Status
                            </button>
                            <button @click="closeTrackingModal()" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-xl text-xs hover:bg-gray-300 transition">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="paymentModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="paymentModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closePaymentModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="paymentModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                    <template x-if="paymentOrder">
                        <form :action="`{{ url('orders') }}/${paymentOrder?.id}/payment`" method="POST">
                            @csrf
                            
                            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                <h3 class="text-xl font-black text-gray-900">Record Payment #<span x-text="paymentOrder?.id"></span></h3>
                                <button type="button" @click="closePaymentModal()" class="text-gray-400 hover:text-gray-900"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="p-6 space-y-6">
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-center flex justify-between">
                                    <div class="text-sm text-gray-500 font-bold">Total: Rs.<span x-text="paymentOrder?.total_amount"></span></div>
                                    <div class="text-sm text-gray-500 font-bold text-green-600">Paid: Rs.<span x-text="paymentOrder?.paid_amount || 0"></span></div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Payment Method</label>
                                    <select name="payment_method" x-model="paymentMethod" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango font-bold text-sm" required>
                                        <option value="cod">Cash on Delivery (Pathao)</option>
                                        <option value="paid">Fully Paid (Advance)</option>
                                        <option value="partial">Partially Paid (Advance)</option>
                                    </select>
                                </div>

                                <div x-show="paymentMethod === 'paid' || paymentMethod === 'partial'" x-transition>
                                    <div class="mb-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Amount to Pay</label>
                                        <input name="amount" x-model="paymentAmount" type="number" step="0.01" min="0" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango" :required="paymentMethod !== 'cod'">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Deposit To Account</label>
                                        <select name="account_id" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango" :required="paymentMethod !== 'cod'">
                                            <option value="">Select Account...</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} (Rs. {{ number_format($account->balance, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Notes (Optional)</label>
                                        <input name="notes" type="text" placeholder="e.g. Fonepay, Bank Transfer" class="w-full rounded-xl border-gray-200 bg-gray-50 py-3 focus:ring-mango">
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                                <button type="button" @click="closePaymentModal()" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancel</button>
                                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white font-bold rounded-xl shadow-lg hover:bg-green-700 transition active:scale-95">Save Payment</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <!-- AlpineJS Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderManager', () => ({
                selectedOrders: [],
                confirmModalOpen: false,
                selectedOrder: null,
                
                paymentModalOpen: false,
                paymentOrder: null,
                paymentAmount: 0,
                paymentMethod: 'cod',

                trackingModalOpen: false,
                trackingData: null,
                trackingLoading: false,
                trackingOrderId: null,
                trackingSteps: ['Pickup', 'In Transit', 'At Hub', 'Out for Delivery', 'Delivered'],
                
                productPrices: {
                    @foreach($products as $product)
                        '{{ $product->id }}:1': {{ $product->price }},
                        @if($product->bundles)
                            @foreach($product->bundles as $bundle)
                                '{{ $product->id }}:{{ $bundle['qty'] }}': {{ $bundle['price'] / $bundle['qty'] }},
                            @endforeach
                        @endif
                    @endforeach
                },
                cities: [],
                zones: [],
                areas: [],

                formData: {
                    customer_name: '',
                    customer_phone: '',
                    address: '',
                    pathao_city_id: '',
                    pathao_zone_id: ''
                },

                editModalOpen: false,
                selectedEditOrder: null,
                editFormData: {
                    customer_name: '',
                    customer_phone: '',
                    city: '',
                    address: '',
                    pathao_city_id: '',
                    pathao_zone_id: '',
                    status: '',
                    items: [],
                    delivery_charge: 0,
                    remarks: ''
                },
                editZones: [],

                // Bulk Spreadsheet State
                bulkModalOpen: false,
                bulkSubmitting: false,
                bulkProcessing: false, // UX-05: Loading state for bulk ship/delete
                bulkRows: [],

                async init() {
                    // Pre-fetch cities when dashboard loads
                    try {
                        const res = await fetch('{{ url("api/pathao/cities") }}');
                        this.cities = await res.json();
                    } catch (e) {
                        console.error('Failed to load cities', e);
                    }
                    // Listen for bulk modal open event from header button
                    window.addEventListener('open-bulk-modal', () => this.openBulkModal());
                },

                selectAll() {
                    const checkboxes = document.querySelectorAll('.order-checkbox');
                    this.selectedOrders = Array.from(checkboxes).map(cb => cb.value);
                },

                deselectAll() {
                    this.selectedOrders = [];
                },

                calculateEditGrandTotal() {
                    let itemsTotal = 0;
                    if (this.editFormData.items) {
                        this.editFormData.items.forEach(item => {
                            itemsTotal += parseFloat(item.total_price || 0);
                        });
                    }
                    let delivery = parseFloat(this.editFormData.delivery_charge || 0);
                    return (itemsTotal + delivery).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                addProduct() {
                    this.editFormData.items.push({
                        id: null,
                        product_id: '',
                        selection: '',
                        quantity: 1,
                        unit_price: 0,
                        total_price: 0
                    });
                },

                removeProduct(index) {
                    this.editFormData.items.splice(index, 1);
                },

                onProductChange(index) {
                    const item = this.editFormData.items[index];
                    if (!item.selection) return;
                    const [productId, bundleQty] = item.selection.split(':');
                    item.product_id = productId;
                    const unitPrice = this.productPrices[item.selection] || 0;
                    const qty = parseInt(bundleQty) || 1;
                    item.unit_price = unitPrice;
                    item.quantity = qty;
                    item.total_price = unitPrice * qty;
                },

                onQuantityChange(index) {
                    const item = this.editFormData.items[index];
                    const qty = parseInt(item.quantity) || 1;
                    if (item.unit_price) {
                        item.total_price = item.unit_price * qty;
                    }
                },

                openEditModal(order) {
                    this.selectedEditOrder = order;
                    this.editFormData.customer_name = order.customer_name;
                    this.editFormData.customer_phone = order.customer_phone;
                    this.editFormData.city = order.city || '';
                    this.editFormData.address = order.address || '';
                    this.editFormData.pathao_city_id = order.pathao_city_id || '';
                    this.editFormData.pathao_zone_id = order.pathao_zone_id || '';
                    this.editFormData.status = order.status || '';
                    this.editFormData.delivery_charge = parseFloat(order.delivery_charge || 0);
                    this.editFormData.remarks = order.remarks || '';
                    
                    if(this.editFormData.pathao_city_id) {
                        this.fetchEditZones();
                    }
                    
                    this.editFormData.items = order.order_items.map(item => {
                        const exactSelection = `${item.product_id}:${item.quantity}`;
                        // Check if this exact combo exists in productPrices (i.e., it's a valid bundle or single)
                        const selection = this.productPrices[exactSelection] !== undefined ? exactSelection : `${item.product_id}:1`;
                        return {
                            id: item.id,
                            product_id: String(item.product_id),
                            selection: selection,
                            quantity: item.quantity,
                            unit_price: item.price_at_purchase,
                            total_price: item.quantity * item.price_at_purchase,
                            color: item.color || '',
                            size: item.size || ''
                        };
                    });
                    
                    this.editModalOpen = true;
                },

                closeEditModal() {
                    this.editModalOpen = false;
                    setTimeout(() => { this.selectedEditOrder = null; }, 300);
                },

                openPaymentModal(order) {
                    this.paymentOrder = order;
                    let remaining = parseFloat(order.total_amount) - parseFloat(order.paid_amount || 0);
                    this.paymentAmount = remaining > 0 ? remaining : order.total_amount;
                    this.paymentMethod = order.payment_method || 'cod';
                    if (remaining <= 0) {
                        this.paymentMethod = 'paid';
                    }
                    this.paymentModalOpen = true;
                },

                closePaymentModal() {
                    this.paymentModalOpen = false;
                    setTimeout(() => { this.paymentOrder = null; }, 300);
                },

                async fetchZones() {
                    this.zones = [];
                    this.formData.pathao_zone_id = '';
                    if (!this.formData.pathao_city_id) return;

                    try {
                        const res = await fetch(`{{ url('api/pathao/zones') }}/${this.formData.pathao_city_id}`);
                        this.zones = await res.json();
                    } catch (e) { console.error(e); }
                },

                async fetchEditZones() {
                    this.editZones = [];
                    const savedZone = this.editFormData.pathao_zone_id;
                    if (!this.editFormData.pathao_city_id) return;

                    try {
                        const res = await fetch(`{{ url('api/pathao/zones') }}/${this.editFormData.pathao_city_id}`);
                        this.editZones = await res.json();
                        // Restore the saved zone after dropdown options are loaded
                        this.$nextTick(() => { this.editFormData.pathao_zone_id = savedZone; });
                    } catch (e) { console.error(e); }
                },

                // === Tracking Modal Methods ===
                async openTrackingModal(orderId) {
                    this.trackingOrderId = orderId;
                    this.trackingModalOpen = true;
                    this.trackingLoading = true;
                    this.trackingData = null;
                    try {
                        const res = await fetch(`{{ url('orders') }}/${orderId}/pathao-details`);
                        this.trackingData = await res.json();
                    } catch (e) {
                        console.error('Failed to load tracking data', e);
                    }
                    this.trackingLoading = false;
                },

                closeTrackingModal() {
                    this.trackingModalOpen = false;
                    setTimeout(() => { this.trackingData = null; this.trackingOrderId = null; }, 300);
                },

                async refreshTrackingStatus() {
                    if (!this.trackingOrderId) return;
                    this.trackingLoading = true;
                    try {
                        const res = await fetch(`{{ url('orders') }}/${this.trackingOrderId}/pathao-details`);
                        this.trackingData = await res.json();
                    } catch (e) { console.error(e); }
                    this.trackingLoading = false;
                },

                getStepIndex() {
                    const status = (this.trackingData?.pathao?.status || '').toLowerCase();
                    if (status.includes('delivered') || status.includes('successful')) return 4;
                    if (status.includes('out for')) return 3;
                    if (status.includes('hub') || status.includes('sorting') || status.includes('last mile')) return 2;
                    if (status.includes('transit') || status.includes('in transit')) return 1;
                    if (status.includes('picked') || status.includes('pickup')) return 0;
                    return -1; // awaiting pickup
                },

                getStatusBadgeClass(status) {
                    const s = (status || '').toLowerCase();
                    if (s.includes('delivered')) return 'bg-green-100 text-green-800';
                    if (s.includes('transit')) return 'bg-indigo-100 text-indigo-800';
                    if (s.includes('picked') || s.includes('pickup')) return 'bg-blue-100 text-blue-800';
                    if (s.includes('hub') || s.includes('sorting')) return 'bg-purple-100 text-purple-800';
                    if (s.includes('out for')) return 'bg-orange-100 text-orange-800';
                    if (s.includes('return')) return 'bg-red-100 text-red-800';
                    if (s.includes('cancel')) return 'bg-gray-100 text-gray-600';
                    return 'bg-yellow-100 text-yellow-800';
                },

                copyTrackingId() {
                    const id = this.trackingData?.order?.pathao_consignment_id;
                    if (id) {
                        navigator.clipboard.writeText(id);
                        alert('Tracking ID copied: ' + id);
                    }
                },

                // === Bulk Spreadsheet Methods ===
                emptyBulkRow() {
                    return { customer_name: '', customer_phone: '', address: '', city: '', product_selection: '', product_id: '', quantity: 1, amount: 0, unit_price: 0, _error: false };
                },

                openBulkModal() {
                    if (this.bulkRows.length === 0) {
                        this.bulkRows = Array.from({ length: 5 }, () => this.emptyBulkRow());
                    }
                    this.bulkModalOpen = true;
                },

                closeBulkModal() {
                    this.bulkModalOpen = false;
                },

                addBulkRow() {
                    this.bulkRows.push(this.emptyBulkRow());
                },

                addBulkRows(n) {
                    for (let i = 0; i < n; i++) this.bulkRows.push(this.emptyBulkRow());
                },

                removeBulkRow(idx) {
                    if (this.bulkRows.length > 1) this.bulkRows.splice(idx, 1);
                },

                onBulkProductChange(idx) {
                    const row = this.bulkRows[idx];
                    if (!row.product_selection) { row.product_id = ''; row.amount = 0; row.unit_price = 0; return; }
                    const parts = row.product_selection.split(':');
                    row.product_id = parts[0];
                    const bundleQty = parseInt(parts[1]) || 1;
                    const bundlePrice = parseFloat(parts[2]) || 0;
                    row.quantity = bundleQty;
                    row.unit_price = bundleQty > 0 ? bundlePrice / bundleQty : 0;
                    row.amount = bundlePrice;
                },

                onBulkQtyChange(idx) {
                    const row = this.bulkRows[idx];
                    if (row.unit_price > 0) {
                        row.amount = row.unit_price * (parseInt(row.quantity) || 1);
                    }
                },

                bulkGrandTotal() {
                    return this.bulkRows.reduce((sum, r) => sum + (parseFloat(r.amount) || 0), 0);
                },

                bulkFilledRows() {
                    return this.bulkRows.filter(r => r.customer_name && r.customer_phone && r.address && r.product_id).length;
                },

                async submitBulkOrders() {
                    // Validate
                    let hasError = false;
                    this.bulkRows.forEach(r => {
                        r._error = false;
                        if (!r.customer_name && !r.customer_phone && !r.address && !r.product_selection) return; // skip empty rows
                        if (!r.customer_name || !r.customer_phone || !r.address || !r.product_id) {
                            r._error = true;
                            hasError = true;
                        }
                    });

                    const validRows = this.bulkRows.filter(r => r.customer_name && r.customer_phone && r.address && r.product_id);
                    if (validRows.length === 0) {
                        alert('Please fill in at least one complete row.');
                        return;
                    }
                    if (hasError) {
                        if (!confirm('Some rows have missing fields (highlighted in red) and will be skipped. Continue with ' + validRows.length + ' valid orders?')) return;
                    }

                    this.bulkSubmitting = true;
                    try {
                        const res = await fetch('{{ route("orders.bulkManualStore") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ orders: validRows.map(r => ({ customer_name: r.customer_name, customer_phone: r.customer_phone, address: r.address, city: r.city, product_id: r.product_id, quantity: parseInt(r.quantity) || 1, amount: parseFloat(r.amount) || 0 })) })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            window.location.href = '{{ route("orders.index", ["status" => "pending"]) }}';
                        } else {
                            alert(data.message || 'Failed to create orders.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Network error. Please try again.');
                    }
                    this.bulkSubmitting = false;
                },

                async bulkDeleteOrders() {
                    if (this.bulkProcessing) return;
                    if (!confirm('Are you sure you want to delete ' + this.selectedOrders.length + ' pending orders? This cannot be undone.')) return;
                    this.bulkProcessing = true;
                    try {
                        const res = await fetch('{{ route("orders.bulkDelete") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ order_ids: this.selectedOrders })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to delete orders.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Network error. Please try again.');
                    }
                    this.bulkProcessing = false;
                },

                async bulkShipOrders() {
                    if (this.bulkProcessing) return;
                    if (!confirm('Ship ' + this.selectedOrders.length + ' orders via Pathao? This will create consignments for all selected orders.')) return;
                    this.bulkProcessing = true;
                    try {
                        const res = await fetch('{{ route("orders.bulkShip") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ order_ids: this.selectedOrders })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            let msg = data.message || 'Orders shipped successfully!';
                            if (data.errors && data.errors.length > 0) {
                                msg += '\n\nFailed orders:\n' + data.errors.join('\n');
                            }
                            alert(msg);
                            window.location.href = '{{ route("orders.index", ["status" => "shipped"]) }}';
                        } else {
                            let msg = data.message || 'Failed to ship orders.';
                            if (data.errors && data.errors.length > 0) {
                                msg += '\n\nReasons:\n' + data.errors.join('\n');
                            }
                            alert(msg);
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Network error. Please try again.');
                    }
                    this.bulkProcessing = false;
                },

                async bulkRejectOrders() {
                    if (!confirm('Reject ' + this.selectedOrders.length + ' confirmed orders? This cannot be undone easily.')) return;
                    try {
                        const res = await fetch('{{ route("orders.bulkStatusUpdate") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ order_ids: this.selectedOrders, status: 'rejected' })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to reject orders.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Network error. Please try again.');
                    }
                }
            }));
        });
    </script>
</x-app-layout>
