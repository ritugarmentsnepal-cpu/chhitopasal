<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
        {{ $orderType === 'custom_print' ? __('Custom Print Orders') : __('Order Management') }}
      </h2>
      @if($status === 'pending' && $orderType === 'custom_print')
        <div class="flex gap-2">
          <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'add-custom-print-modal')" class="bg-purple-600 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg hover:bg-purple-700 transition duration-150 active:scale-95 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            <span class="hidden sm:inline">New Print Order</span>
          </button>
        </div>
      @elseif($status === 'pending' && $orderType === 'standard')
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

  <div class="py-6" x-data="orderManager()">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
      
      @if (session('success'))
        <div class="mb-6 bg-green-50 text-green-700 border border-green-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span class="font-bold">{{ session('success') }}</span>
        </div>
      @endif
      @if (session('error'))
        <div class="mb-6 bg-red-50 text-red-700 border border-red-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span class="font-bold">{{ session('error') }}</span>
        </div>
      @endif

      <!-- Order Type Toggle -->
      <div class="flex items-center gap-2 mb-4">
        <a href="{{ request()->fullUrlWithQuery(['order_type' => 'standard']) }}" class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all {{ $orderType === 'standard' ? 'bg-gray-900 text-white shadow-lg' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50 hover:text-gray-900' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
          Standard Orders
        </a>
        <a href="{{ request()->fullUrlWithQuery(['order_type' => 'custom_print']) }}" class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all {{ $orderType === 'custom_print' ? 'bg-purple-600 text-white shadow-lg' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50 hover:text-gray-900' }}">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
          Custom Print Orders
        </a>
      </div>

      <!-- Tabs Navigation -->
      <div class="bg-white p-2 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex overflow-x-auto no-scrollbar mb-6">
        @php
          if ($orderType === 'custom_print') {
            $tabs = [
              'pending' => 'New Requests',
              'design' => 'Design Phase',
              'production' => 'In Production',
              'ready_to_ship' => 'Ready to Ship',
              'shipped' => 'Shipped',
              'delivered' => 'Delivered',
              'rejected' => 'Rejected'
            ];
          } else {
            $tabs = [
              'pending' => 'Pending',
              'confirmed' => 'Confirmed',
              'shipped' => 'Shipped',
              'delivered' => 'Delivered',
              'return_delivered' => 'Returned',
              'failed' => 'Failed',
              'rejected' => 'Rejected'
            ];
          }
        @endphp
        @foreach($tabs as $key => $label)
          <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}" class="px-6 py-2.5 rounded-full font-bold whitespace-nowrap transition-all flex-1 text-center {{ $status === $key ? ($orderType === 'custom_print' ? 'bg-purple-600 text-white shadow-sm' : 'bg-mango text-gray-900 shadow-sm') : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
            {{ $label }}
          </a>
        @endforeach
      </div>


      @if($status === 'return_delivered')
      <!-- Damage Report Link -->
      <div class="mb-4 flex justify-end">
        <a href="{{ route('orders.damageReport') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition active:scale-95">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
          Damage Report
        </a>
      </div>
      @endif

      <!-- Filter Bar -->
      @if($status !== 'shipped')
      <form method="GET" action="{{ route('orders.index') }}" class="mb-6 flex gap-4 items-center bg-white p-3 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
        <input type="hidden" name="status" value="{{ $status }}">
        @if(request('pathao_filter'))
          <input type="hidden" name="pathao_filter" value="{{ request('pathao_filter') }}">
        @endif
        @if(request('shipped_date_filter'))
          <input type="hidden" name="shipped_date_filter" value="{{ request('shipped_date_filter') }}">
        @endif
        
        <div class="flex-1 relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          </div>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Order ID, Name, or Phone..." class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 transition-colors font-medium">
        </div>

        <div class="w-48 relative">
          <select name="date_filter" onchange="this.form.submit()" class="block w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 transition-colors font-medium appearance-none">
            <option value="">All Time</option>
            <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
            <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
            <option value="this_week" {{ request('date_filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
            <option value="this_month" {{ request('date_filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
          </select>
        </div>

        <button type="submit" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 transition active:scale-95 whitespace-nowrap">
          Search
        </button>
        @if(request('search') || request('date_filter') || request('pathao_filter') || request('shipped_date_filter'))
          <a href="{{ route('orders.index', ['status' => $status]) }}" class="text-gray-500 hover:text-red-500 font-bold px-4 transition">Clear</a>
        @endif
      </form>
      @endif

      <!-- Orders Table -->
      <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
        <div x-show="selectedOrders.length > 0" x-cloak class="border-b px-6 py-3 flex items-center justify-between transition-all
          {{ in_array($status, ['pending', 'design']) ? 'bg-red-50 border-red-100' : (in_array($status, ['confirmed', 'production', 'ready_to_ship']) ? 'bg-emerald-50 border-emerald-100' : 'bg-blue-50 border-blue-100') }}">
          <span class="text-sm font-bold {{ in_array($status, ['pending', 'design']) ? 'text-red-900' : (in_array($status, ['confirmed', 'production', 'ready_to_ship']) ? 'text-emerald-900' : 'text-blue-900') }}"><span x-text="selectedOrders.length"></span> orders selected</span>
          <div class="flex items-center gap-2">
            @if(in_array($status, ['pending', 'design']))
              <button type="button" @click="bulkDeleteOrders()" :disabled="bulkProcessing" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-sm flex items-center gap-2 transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="!bulkProcessing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                <svg x-show="bulkProcessing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-text="bulkProcessing ? 'Deleting...' : 'Delete Selected'"></span>
              </button>
            @elseif($status === 'confirmed' || $status === 'ready_to_ship')
              <button type="button" @click="bulkShipOrders()" :disabled="bulkProcessing" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-sm flex items-center gap-2 transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="!bulkProcessing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                <svg x-show="bulkProcessing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-text="bulkProcessing ? 'Shipping...' : 'Ship All via Pathao'"></span>
              </button>
            @elseif(!in_array($status, ['pending', 'design', 'production']))
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
              <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                <th class="p-4 border-b border-gray-100 rounded-tl-3xl w-10">
                  <input type="checkbox" class="rounded border-gray-300 text-mango focus:ring-mango" 
                      @change="$event.target.checked ? selectAll() : deselectAll()"
                      :checked="selectedOrders.length === {{ count($orders) }} && {{ count($orders) }} > 0">
                </th>
                <th class="p-4 border-b border-gray-100">ID & Date</th>
                <th class="p-4 border-b border-gray-100">Customer</th>
                <th class="p-4 border-b border-gray-100">Location</th>
                @if($status === 'shipped')
                  <th class="p-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                      Pathao Status
                      <span x-show="autoRefreshRunning" x-cloak class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                        <span x-text="autoRefreshProgress"></span>
                      </span>
                      <span x-show="!autoRefreshRunning && autoRefreshProgress === ''" x-cloak></span>
                    </div>
                  </th>
                @endif
                <th class="p-4 border-b border-gray-100">Items & Total</th>
                <th class="p-4 border-b border-gray-100 text-right rounded-tr-3xl">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($orders as $order)
                <tr class="hover:bg-gray-50/50 transition-colors group">
                  <td class="p-4 align-top">
                    <input type="checkbox" class="rounded border-gray-300 text-mango focus:ring-mango order-checkbox" 
                        value="{{ $order->id }}"
                        x-model="selectedOrders">
                  </td>
                  <td class="p-4 align-top">
                    <a href="{{ route('orders.show', $order) }}" class="font-black text-gray-900 hover:text-indigo-600 transition underline decoration-gray-200 hover:decoration-indigo-400 underline-offset-2">#{{ $order->id }}</a>
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
                      <div class="mt-1 text-xs font-bold text-mango flex items-center gap-1">
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
                    <button @click="openTrackingModal({{ $order->id }})" id="pathao-badge-{{ $order->id }}" data-order-id="{{ $order->id }}" class="pathao-status-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $badgeClass }} cursor-pointer hover:shadow-md hover:scale-105 transition-all duration-150" title="Click for live tracking">
                      <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                      <span class="pathao-status-text">{{ $order->pathao_status ?? 'Awaiting Pickup' }}</span>
                      <svg class="w-2.5 h-2.5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </button>
                    <div class="text-[10px] text-gray-400 mt-1 pathao-updated-text" id="pathao-updated-{{ $order->id }}">
                      @if($order->pathao_status_updated_at)
                        {{ \Carbon\Carbon::parse($order->pathao_status_updated_at)->diffForHumans() }}
                      @else
                        never synced
                      @endif
                    </div>
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
                    
                    @if(is_array($order->mockup_files ?? null) && count($order->mockup_files) > 0)
                      <div class="mt-3">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Generated Mockups</div>
                        <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                          @foreach($order->mockup_files as $mockup)
                            <a href="{{ '/storage/' . ($mockup) }}" target="_blank" class="shrink-0 w-16 h-16 rounded-lg border border-gray-200 overflow-hidden hover:border-indigo-500 transition block">
                              <img src="{{ '/storage/' . ($mockup) }}" class="w-full h-full object-cover">
                            </a>
                          @endforeach
                        </div>
                      </div>
                    @endif
                    @if($orderType === 'custom_print')
                      <div class="mt-4">
                        @include('orders.partials.production_tracker', ['order' => $order])
                      </div>
                    @endif
                  </td>
                  <td class="p-4 align-top text-right w-[320px]">
                    <div class="flex flex-col gap-2 w-full ml-auto">
                      @if(in_array($status, ['pending', 'confirmed', 'design', 'production', 'ready_to_ship']))
                        @if($orderType === 'custom_print')
                          @include('orders.partials.custom_print_edit_modal')
                          @include('orders.partials.mockup_studio_modal')
                          
                          <div class="flex gap-2 w-full">
                            <button x-on:click.prevent="$dispatch('open-modal', 'custom-print-edit-modal-{{ $order->id }}')" class="flex-1 text-center px-3 py-2 bg-gray-50 text-gray-700 font-bold rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors text-xs flex items-center justify-center gap-1">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                              {{ in_array($status, ['pending', 'design']) ? 'Review' : 'Edit' }}
                            </button>
                            <button x-on:click.prevent="$dispatch('open-modal', 'mockup-studio-{{ $order->id }}')" class="flex-1 text-center px-3 py-2 bg-indigo-50 text-indigo-700 font-bold rounded-xl border border-indigo-200 hover:bg-indigo-100 transition-colors text-xs flex items-center justify-center gap-1">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                              Mockup
                            </button>
                          </div>
                        @else
                          <button @click="openEditModal({{ $order }})" class="inline-flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-200 active:scale-95 transition-all w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            {{ in_array($status, ['pending', 'design']) ? 'Review & Process' : 'Edit Details' }}
                          </button>
                        @endif
                      @endif

                      @if(in_array($status, ['pending', 'confirmed', 'design', 'production', 'ready_to_ship']))
                        <form action="{{ route('orders.status', $order) }}" method="POST" class="w-full mt-1">
                          @csrf
                          <input type="hidden" name="status" value="rejected">
                          <button type="submit" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-bold py-2 px-2 rounded-lg text-xs transition flex items-center justify-center">
                            Reject Order
                          </button>
                        </form>
                      @endif

                      @if($status === 'confirmed' || $status === 'ready_to_ship')
                        <form action="{{ route('orders.ship', $order) }}" method="POST" class="w-full mt-1">
                          @csrf
                          <button type="submit" name="print_type" value="both" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-2 rounded-lg transition text-xs shadow-sm text-center">Ship via Pathao</button>
                        </form>
                        
                        @if($orderType === 'custom_print' && $status === 'ready_to_ship')
                        <form action="{{ route('orders.status', $order) }}" method="POST" class="w-full mt-1">
                          @csrf
                          <input type="hidden" name="status" value="delivered">
                          <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-2 rounded-lg transition text-xs shadow-sm text-center">Mark Delivered (Manual)</button>
                        </form>
                        @endif
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
                        <button type="button" @click="openReturnModal({{ $order->load('orderItems.product') }})" class="w-full bg-orange-100 text-orange-700 hover:bg-orange-200 font-bold py-2 px-2 rounded-lg text-xs transition flex items-center justify-center gap-1 mt-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                          Verify Return
                        </button>
                      @elseif($order->return_verified_at)
                        <div class="w-full mt-1">
                          <div class="text-xs font-bold text-green-600 flex items-center justify-center gap-1 w-full bg-green-50 py-2 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Return Verified
                          </div>
                          @php
                            $goodTotal = $order->orderItems->sum('returned_good_qty');
                            $damagedTotal = $order->orderItems->sum('returned_damaged_qty');
                          @endphp
                          @if($goodTotal > 0 || $damagedTotal > 0)
                          <div class="flex gap-2 mt-1 justify-center">
                            @if($goodTotal > 0)
                              <span class="text-[10px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded">✅ {{ $goodTotal }} good</span>
                            @endif
                            @if($damagedTotal > 0)
                              <span class="text-[10px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded">❌ {{ $damagedTotal }} damaged</span>
                            @endif
                          </div>
                          @endif
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
        @if($orders->hasPages() || $orders->total() > 0)
          <div class="p-4 border-t border-gray-100 bg-gray-50 rounded-b-[24px] flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-gray-500" x-data="{ customPerPage: '' }">
              <span class="font-bold">Show</span>
              <div class="relative">
                <select onchange="window.location.href=this.value" class="appearance-none bg-white border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-xs font-bold text-gray-700 focus:border-gray-900 focus:ring focus:ring-gray-900/10 transition-colors cursor-pointer">
                  @foreach([20, 50, 75, 100] as $size)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ request('per_page', 20) == $size ? 'selected' : '' }}>{{ $size }}</option>
                  @endforeach
                  <option value="{{ request()->fullUrlWithQuery(['per_page' => $orders->total(), 'page' => 1]) }}" {{ request('per_page', 20) == $orders->total() && $orders->total() > 100 ? 'selected' : '' }}>All ({{ $orders->total() }})</option>
                </select>
                <svg class="absolute right-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </div>
              <span class="text-gray-300">or</span>
              <form @submit.prevent="if(customPerPage >= 1 && customPerPage <= 500) window.location.href='{{ request()->fullUrlWithQuery(['page' => 1]) }}&per_page=' + customPerPage" class="flex items-center gap-1">
                <input x-model="customPerPage" type="number" min="1" max="500" placeholder="#" class="w-14 bg-white border border-gray-200 rounded-lg px-2 py-1.5 text-xs font-bold text-gray-700 focus:border-gray-900 focus:ring focus:ring-gray-900/10 transition-colors text-center">
                <button type="submit" class="bg-gray-900 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-lg hover:bg-black transition">Go</button>
              </form>
              <span class="font-medium">per page</span>
              <span class="text-gray-300 mx-1">·</span>
              <span class="font-medium">{{ $orders->total() }} total</span>
            </div>
            <div>
              {{ $orders->links() }}
            </div>
          </div>
        @endif
      </div>
    </div>

    {{-- ── Modals (PHASE-1.2: extracted to partials, loaded only on tabs that can use them) ── --}}

    @if($orderType === 'standard' && $status === 'pending')
      @include('orders.partials.list.bulk_upload_modal')
      @include('orders.partials.list.manual_order_modal')
    @endif

    @if($orderType === 'standard' && in_array($status, ['pending', 'confirmed']))
      @include('orders.partials.list.edit_order_modal')
    @endif

    @if($status === 'shipped')
      @include('orders.partials.list.tracking_modal')
    @endif

    {{-- Payment is reachable from every tab whose orders can still take money --}}
    @include('orders.partials.list.payment_modal')

    @if($status === 'return_delivered')
      @include('orders.partials.list.return_modal')
    @endif

  </div>

  @include('orders.partials.list.manager_script')

{{-- Custom Print Order Modal --}}
@if(isset($orderType) && $orderType === 'custom_print')
  @include('orders.partials.custom_print_form')
@endif

</x-app-layout>
