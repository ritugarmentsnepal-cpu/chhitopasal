<x-app-layout>
  <x-slot name="header">
    <div>
      <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
        {{ __('Accounting Hub') }}
      </h2>
      <p class="text-sm font-bold text-gray-500 mt-1">Manage your finances, invoices, and reports.</p>
    </div>
  </x-slot>

  @php
    $tabs = [
      'dashboard' => ['label' => 'Dashboard', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
      'pos' => ['label' => 'POS', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
      'invoices' => ['label' => 'Invoices', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
      'returns' => ['label' => 'Returns', 'icon' => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
      'parties' => ['label' => 'CRM', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
      'purchases' => ['label' => 'Purchases', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
      'expenses' => ['label' => 'Expenses', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
      'banking' => ['label' => 'Banking', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
      'inventory' => ['label' => 'Inventory', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
      'reports' => ['label' => 'Reports', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
      'payroll' => ['label' => 'Payroll & HR', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
      'activity' => ['label' => 'Logs', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
  @endphp

  <div class="py-6">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Horizontal Tab Bar -->
      <div class="mb-6 bg-white p-2 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-gray-100 flex overflow-x-auto no-scrollbar gap-1">
        @foreach($tabs as $key => $t)
          <a href="{{ route('accounting.index', ['tab' => $key]) }}" class="flex items-center gap-1.5 px-4 py-2.5 rounded-full font-bold text-sm whitespace-nowrap transition-all {{ $tab === $key ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 ' }}">
            <svg class="w-4 h-4 shrink-0 {{ $tab === $key ? 'text-gray-900' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"></path>
            </svg>
            <span class="hidden sm:inline">{{ $t['label'] }}</span>
          </a>
        @endforeach
      </div>

      <!-- Flash Messages -->
      @if(session('success'))
        <div class="bg-green-50 text-green-700 border border-green-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3 mb-6">
          <div class="bg-green-100 p-1.5 rounded-full"><svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
          <p class="font-bold">{{ session('success') }}</p>
        </div>
      @endif
      @if(session('error'))
        <div class="bg-red-50 text-red-700 border border-red-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3 mb-6">
          <div class="bg-red-100 p-1.5 rounded-full"><svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></div>
          <p class="font-bold">{{ session('error') }}</p>
        </div>
      @endif

      <!-- Tab Content -->
      <div class="transition-opacity duration-300">
        @include('accounting.tabs.' . $tab)
      </div>
    </div>
  </div>
</x-app-layout>
