<x-app-layout>
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen bg-gray-50/50">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-cloak x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Mobile Horizontal Tab Bar (visible on small screens only) -->
        <div class="lg:hidden fixed top-[72px] left-0 right-0 z-30 bg-white border-b border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 px-3 py-2 overflow-x-auto no-scrollbar">
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
                        'activity' => ['label' => 'Logs', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ];
                @endphp
                @foreach($tabs as $key => $t)
                    <a href="{{ route('accounting.index', ['tab' => $key]) }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs whitespace-nowrap transition-all {{ $tab === $key ? 'bg-mango/20 text-gray-900' : 'text-gray-500 hover:bg-gray-50' }}">
                        <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $tab === $key ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"></path>
                        </svg>
                        {{ $t['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Desktop Sidebar (hidden on mobile) -->
        <div class="hidden lg:block w-64 bg-white border-r border-gray-100 flex-shrink-0 pt-6">
            <h2 class="px-6 text-xl font-black text-gray-900 mb-6">Accounting Hub</h2>
            <nav class="space-y-1">
                @foreach($tabs as $key => $t)
                    <a href="{{ route('accounting.index', ['tab' => $key]) }}" class="flex items-center gap-3 px-6 py-3 font-bold text-sm transition-all border-r-4 {{ $tab === $key ? 'bg-mango/10 text-gray-900 border-mango' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-5 h-5 {{ $tab === $key ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"></path>
                        </svg>
                        {{ $t['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0 p-4 pt-[120px] lg:p-8 lg:pt-8">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-sm mb-6">
                    <p class="font-bold text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm mb-6">
                    <p class="font-bold text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            @include('accounting.tabs.' . $tab)
        </div>
    </div>
</x-app-layout>
