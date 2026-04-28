<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-[72px]">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group shrink-0">
                    <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center transform group-hover:scale-105 transition-transform shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
                    </div>
                    <h1 class="text-xl font-black tracking-tight text-gray-900 hidden sm:block">Mission <span class="text-mango">Control</span></h1>
                </a>

                <!-- Desktop Navigation Links -->
                <div class="hidden sm:flex items-center gap-2 h-full ml-4">
                    <a href="{{ route('dashboard') }}" class="px-4 h-full flex items-center font-bold text-sm transition-all border-b-2 {{ request()->routeIs('dashboard') ? 'border-mango text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-200' }}">
                        Dashboard
                    </a>
                    
                    @if(in_array(auth()->user()->role, ['admin', 'manager', 'operational_staff']))
                        <a href="{{ route('orders.index') }}" class="px-4 h-full flex items-center font-bold text-sm transition-all border-b-2 {{ request()->routeIs('orders.*') ? 'border-mango text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-200' }}">
                            Orders
                        </a>
                        <a href="{{ route('products.index') }}" class="px-4 h-full flex items-center font-bold text-sm transition-all border-b-2 {{ request()->routeIs('products.*') ? 'border-mango text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-200' }}">
                            Products
                        </a>
                        <a href="{{ route('categories.index') }}" class="px-4 h-full flex items-center font-bold text-sm transition-all border-b-2 {{ request()->routeIs('categories.*') ? 'border-mango text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-200' }}">
                            Categories
                        </a>
                        <a href="{{ route('customers.index') }}" class="px-4 h-full flex items-center font-bold text-sm transition-all border-b-2 {{ request()->routeIs('customers.*') ? 'border-mango text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-200' }}">
                            Customers
                        </a>
                        <a href="{{ route('pathao.index') }}" class="px-4 h-full flex items-center font-bold text-sm transition-all border-b-2 {{ request()->routeIs('pathao.*') ? 'border-mango text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-200' }}">
                            Pathao Manager
                        </a>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin', 'manager', 'accountant']))
                        <div class="hidden sm:flex sm:items-center h-full px-4 border-b-2 {{ request()->routeIs('accounting.*') || request()->routeIs('purchases.*') || request()->routeIs('expenses.*') ? 'border-mango' : 'border-transparent' }}">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center font-bold text-sm transition-colors {{ request()->routeIs('accounting.*') || request()->routeIs('purchases.*') || request()->routeIs('expenses.*') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' }}">
                                        <div>Accounting</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('accounting.index')" class="font-bold {{ request()->routeIs('accounting.*') ? 'text-mango' : '' }}">
                                        {{ __('Dashboard') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('purchases.index')" class="font-bold {{ request()->routeIs('purchases.*') ? 'text-mango' : '' }}">
                                        {{ __('Purchases') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('expenses.index')" class="font-bold {{ request()->routeIs('expenses.*') ? 'text-mango' : '' }}">
                                        {{ __('Expenses') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('users.index') }}" class="px-4 h-full flex items-center font-bold text-sm transition-all border-b-2 {{ request()->routeIs('users.*') ? 'border-mango text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-200' }}">
                            Staff
                        </a>

                        <div class="hidden sm:flex sm:items-center h-full px-4 border-b-2 {{ request()->routeIs('settings.*') ? 'border-mango' : 'border-transparent' }}">
                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center font-bold text-sm transition-colors {{ request()->routeIs('settings.*') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' }}">
                                        <div>Settings</div>
                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('settings.index', ['tab' => 'frontend'])" class="font-bold {{ request()->query('tab') == 'frontend' || (!request()->has('tab') && request()->routeIs('settings.*')) ? 'text-mango' : '' }}">
                                        {{ __('Frontend') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('settings.index', ['tab' => 'erp'])" class="font-bold {{ request()->query('tab') == 'erp' ? 'text-mango' : '' }}">
                                        {{ __('ERP & Invoicing') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('settings.index', ['tab' => 'integrations'])" class="font-bold {{ request()->query('tab') == 'integrations' ? 'text-mango' : '' }}">
                                        {{ __('Integrations') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('settings.index', ['tab' => 'staff'])" class="font-bold {{ request()->query('tab') == 'staff' ? 'text-mango' : '' }}">
                                        {{ __('Staff Roles') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('settings.index', ['tab' => 'automation'])" class="font-bold {{ request()->query('tab') == 'automation' ? 'text-mango' : '' }}">
                                        {{ __('Automation Rules') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <a href="{{ route('home') }}" target="_blank" class="mr-6 text-sm font-bold text-gray-500 hover:text-wildOrchid transition-colors flex items-center gap-1">
                    View Store <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
                
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-gray-100 rounded-xl text-sm font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <div class="w-6 h-6 bg-gray-900 rounded-full text-white flex items-center justify-center text-[10px] mr-2">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="font-bold">
                            {{ __('My Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();" class="text-red-500 font-bold hover:bg-red-50">
                                {{ __('Sign Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-900 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out active:scale-95">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-transition class="sm:hidden border-t border-gray-100 bg-white">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="font-bold">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(in_array(auth()->user()->role, ['admin', 'manager', 'operational_staff']))
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')" class="font-bold">
                    {{ __('Orders') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.index')" class="font-bold">
                    {{ __('Products') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.index')" class="font-bold">
                    {{ __('Categories') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')" class="font-bold">
                    {{ __('Customers') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pathao.index')" :active="request()->routeIs('pathao.*')" class="font-bold text-mango">
                    {{ __('Pathao Manager') }}
                </x-responsive-nav-link>
            @endif
            @if(in_array(auth()->user()->role, ['admin', 'manager', 'accountant']))
                <div class="pt-2 pb-1 border-t border-gray-100 mt-2 bg-gray-50/50">
                    <div class="px-4 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Accounting & Finance</div>
                    <x-responsive-nav-link :href="route('accounting.index')" :active="request()->routeIs('accounting.*')" class="font-bold pl-8">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*')" class="font-bold pl-8">
                        {{ __('Purchases') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')" class="font-bold pl-8">
                        {{ __('Expenses') }}
                    </x-responsive-nav-link>
                </div>
            @endif
            @if(auth()->user()->role === 'admin')
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" class="font-bold">
                    {{ __('Staff') }}
                </x-responsive-nav-link>
                
                <div class="pt-2 pb-1 border-t border-gray-100 mt-2 bg-gray-50/50">
                    <div class="px-4 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Settings</div>
                    <x-responsive-nav-link :href="route('settings.index', ['tab' => 'frontend'])" class="font-bold pl-8">
                        {{ __('Frontend & Branding') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('settings.index', ['tab' => 'erp'])" class="font-bold pl-8">
                        {{ __('ERP & Invoicing') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('settings.index', ['tab' => 'integrations'])" class="font-bold pl-8">
                        {{ __('API Integrations') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('settings.index', ['tab' => 'staff'])" class="font-bold pl-8">
                        {{ __('Staff Roles (RBAC)') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('settings.index', ['tab' => 'automation'])" class="font-bold pl-8">
                        {{ __('Automation Rules') }}
                    </x-responsive-nav-link>
                </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-100 bg-gray-50">
            <div class="px-4 mb-2">
                <div class="font-black text-base text-gray-900">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="space-y-1">
                <a href="{{ route('home') }}" target="_blank" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-bold text-wildOrchid hover:bg-gray-100 transition duration-150 ease-in-out">
                    View Store
                </a>
                <x-responsive-nav-link :href="route('profile.edit')" class="font-bold">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();" class="text-red-500 font-bold">
                        {{ __('Sign Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
