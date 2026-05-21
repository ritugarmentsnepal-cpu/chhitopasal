<aside class="fixed inset-y-0 left-0 z-50 bg-white dark:bg-gray-900 border-r border-gray-100 dark:border-gray-800 transform transition-all duration-300 ease-in-out md:translate-x-0 md:static md:inset-0 flex flex-col shadow-[4px_0_24px_rgb(0,0,0,0.02)] dark:shadow-none" 
      :class="{
          'translate-x-0': sidebarOpen, 
          '-translate-x-full': !sidebarOpen,
          'w-[72px]': sidebarCollapsed,
          'w-64': !sidebarCollapsed
      }">
    <!-- Sidebar Header (Logo) -->
    <div class="h-[72px] flex items-center border-b border-gray-50/50 dark:border-gray-800 shrink-0" :class="sidebarCollapsed ? 'px-3 justify-center' : 'px-6'">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group" :class="sidebarCollapsed ? 'justify-center' : 'w-full'">
            <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center transform group-hover:scale-105 transition-transform shadow-[0_4px_12px_rgb(17,24,39,0.3)] shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
            </div>
            <h1 x-show="!sidebarCollapsed" x-transition.opacity class="text-xl font-black tracking-tight text-gray-900 dark:text-white whitespace-nowrap">Mission <span class="text-mango">Control</span></h1>
        </a>
        <button @click="sidebarOpen = false" x-show="!sidebarCollapsed" class="md:hidden text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 ml-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Sidebar Navigation Links -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar py-6 space-y-8" :class="sidebarCollapsed ? 'px-2' : 'px-4'">
        
        <!-- Main Section -->
        <div>
            <div x-show="!sidebarCollapsed" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 px-3">Overview</div>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-mango/10 text-mango shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Dashboard">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Dashboard</span>
                </a>
                @if(auth()->user()->hasPermission('analytics'))
                    <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('analytics.*') ? 'bg-mango/10 text-mango shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Analytics">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('analytics.*') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Analytics</span>
                    </a>
                @endif
            </div>
        </div>

        @if(auth()->user()->hasPermission('orders') || auth()->user()->hasPermission('products') || auth()->user()->hasPermission('categories') || auth()->user()->hasPermission('customers'))
            <!-- E-Commerce Section -->
            <div>
                <div x-show="!sidebarCollapsed" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 px-3">E-Commerce</div>
                <div class="space-y-1">
                    @if(auth()->user()->hasPermission('orders'))
                    <a href="{{ route('orders.index') }}" class="flex items-center justify-between py-2.5 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('orders.*') && !request()->routeIs('orders.bulkBatches') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-lg shadow-gray-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Orders">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('orders.*') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity>Orders</span>
                        </div>
                        @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                            <span x-show="!sidebarCollapsed" class="bg-mango text-gray-900 py-0.5 px-2 rounded-full text-[10px] font-black">{{ $pendingOrdersCount }}</span>
                        @endif
                    </a>
                    @if(Route::has('orders.bulkBatches'))
                    <a href="{{ route('orders.bulkBatches') }}" class="flex items-center gap-3 py-2 rounded-xl font-bold text-xs transition-all {{ request()->routeIs('orders.bulkBatches') ? 'bg-mango/10 text-mango shadow-sm' : 'text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3 ml-8'" title="Bulk History">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('orders.bulkBatches') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Bulk History</span>
                    </a>
                    @endif
                    @endif
                    @if(auth()->user()->hasPermission('products'))
                    <a href="{{ route('products.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('products.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-lg shadow-gray-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Products">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('products.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Products</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('categories'))
                    <a href="{{ route('categories.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('categories.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-lg shadow-gray-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Categories">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Categories</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('customers'))
                    <a href="{{ route('customers.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('customers.*') ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-lg shadow-gray-900/20' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Customers">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('customers.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Customers</span>
                    </a>
                    @endif
                </div>
            </div>

            @if(auth()->user()->hasPermission('pathao'))
            <!-- Fulfillment Section -->
            <div>
                <div x-show="!sidebarCollapsed" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 px-3">Fulfillment</div>
                <div class="space-y-1">
                    <a href="{{ route('pathao.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-bold text-sm transition-all {{ request()->routeIs('pathao.*') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Pathao Manager">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('pathao.*') ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity>Pathao Manager</span>
                    </a>
                </div>
            </div>
            @endif
        @endif

        @if(auth()->user()->hasPermission('accounting') || auth()->user()->hasPermission('purchases') || auth()->user()->hasPermission('expenses'))
            <!-- Finance Section -->
            <div x-data="{ open: {{ request()->routeIs('accounting.*') || request()->routeIs('purchases.*') || request()->routeIs('expenses.*') ? 'true' : 'false' }} }">
                <template x-if="!sidebarCollapsed">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold text-sm transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Finance
                        </div>
                        <svg class="w-4 h-4 transition-transform transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </template>
                <template x-if="sidebarCollapsed">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">💰</div>
                </template>
                <div x-show="open || sidebarCollapsed" x-collapse class="space-y-1" :class="sidebarCollapsed ? '' : 'pl-11 pr-3 pt-2'">
                    @if(auth()->user()->hasPermission('accounting'))
                    <a href="{{ route('accounting.index') }}" class="block py-2 text-sm font-bold transition-colors {{ request()->routeIs('accounting.*') ? 'text-mango' : 'text-gray-400 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'text-center text-[10px]' : ''" title="Accounting">
                        <span x-show="!sidebarCollapsed">Overview</span>
                        <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('accounting.*') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('purchases'))
                    <a href="{{ route('purchases.index') }}" class="block py-2 text-sm font-bold transition-colors {{ request()->routeIs('purchases.*') ? 'text-mango' : 'text-gray-400 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Purchases">
                        <span x-show="!sidebarCollapsed">Purchases</span>
                        <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('purchases.*') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('expenses'))
                    <a href="{{ route('expenses.index') }}" class="block py-2 text-sm font-bold transition-colors {{ request()->routeIs('expenses.*') ? 'text-mango' : 'text-gray-400 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Expenses">
                        <span x-show="!sidebarCollapsed">Expenses</span>
                        <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('expenses.*') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </a>
                    @endif
                </div>
            </div>
        @endif

        @if(auth()->user()->hasPermission('settings') || auth()->user()->hasPermission('users'))
            <!-- Administration -->
            <div x-data="{ open: {{ request()->routeIs('settings.*') || request()->routeIs('users.*') ? 'true' : 'false' }} }">
                <template x-if="!sidebarCollapsed">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold text-sm transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Settings
                        </div>
                        <svg class="w-4 h-4 transition-transform transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </template>
                <template x-if="sidebarCollapsed">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">⚙️</div>
                </template>
                <div x-show="open || sidebarCollapsed" x-collapse class="space-y-1" :class="sidebarCollapsed ? '' : 'pl-11 pr-3 pt-2'">
                    @if(auth()->user()->hasPermission('settings'))
                    <a href="{{ route('settings.index', ['tab' => 'frontend']) }}" class="block py-2 text-sm font-bold transition-colors {{ request()->routeIs('settings.*') ? 'text-mango' : 'text-gray-400 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Settings">
                        <span x-show="!sidebarCollapsed">General Settings</span>
                        <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('settings.*') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </a>
                    <a href="{{ route('activity-log.index') }}" class="block py-2 text-sm font-bold transition-colors {{ request()->routeIs('activity-log.*') ? 'text-mango' : 'text-gray-400 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Activity Log">
                        <span x-show="!sidebarCollapsed">Activity Log</span>
                        <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('activity-log.*') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('users'))
                    <a href="{{ route('users.index') }}" class="block py-2 text-sm font-bold transition-colors {{ request()->routeIs('users.*') ? 'text-mango' : 'text-gray-400 hover:text-gray-900 dark:hover:text-white' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Staff & Roles">
                        <span x-show="!sidebarCollapsed">Staff & Roles</span>
                        <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('users.*') ? 'text-mango' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </a>
                    @endif
                </div>
            </div>
        @endif
        
    </div>

    <!-- Sidebar Footer -->
    <div class="border-t border-gray-50/50 dark:border-gray-800" :class="sidebarCollapsed ? 'p-2' : 'p-4'">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl flex items-center" :class="sidebarCollapsed ? 'p-2 justify-center' : 'p-4 justify-between'">
            <div x-show="!sidebarCollapsed">
                <p class="text-xs font-bold text-gray-400">System Status</p>
                <p class="text-sm font-black text-green-500 flex items-center gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    All Systems Go
                </p>
            </div>
            <span x-show="sidebarCollapsed" class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse" title="All Systems Operational"></span>
        </div>
    </div>
</aside>
<!-- Mobile Overlay -->
<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm md:hidden" @click="sidebarOpen = false"></div>
