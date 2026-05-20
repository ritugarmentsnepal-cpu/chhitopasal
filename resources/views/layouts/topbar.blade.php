<div x-data="{ searchFocused: false }" @keydown.meta.k.window.prevent="$refs.globalSearch.focus()" class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 sticky top-0 z-40 h-[72px] flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-[0_4px_20px_rgb(0,0,0,0.02)] dark:shadow-none">
    <!-- Left side: Sidebar Toggle & Search -->
    <div class="flex items-center gap-4 flex-1">
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white focus:outline-none transition-colors active:scale-95 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 md:hidden">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Sidebar Collapse Toggle (desktop only) -->
        <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden md:flex text-gray-400 hover:text-gray-900 dark:hover:text-white focus:outline-none transition-colors p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg x-show="!sidebarCollapsed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
            <svg x-show="sidebarCollapsed" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </button>

        <!-- Global Search -->
        <form action="{{ route('orders.index') }}" method="GET" class="hidden sm:flex items-center max-w-md w-full relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-mango transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
            <input x-ref="globalSearch" type="text" name="search" class="block w-full pl-10 pr-12 py-2 border border-gray-200 dark:border-gray-700 rounded-xl leading-5 bg-gray-50 dark:bg-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-mango/20 focus:border-mango sm:text-sm transition-all shadow-sm font-medium" placeholder="Search orders, products, customers...">
            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                <kbd class="hidden sm:inline-block border border-gray-200 dark:border-gray-600 rounded px-2 py-0.5 text-xs font-bold text-gray-400 bg-white dark:bg-gray-700 shadow-sm">⌘K</kbd>
            </div>
        </form>
    </div>

    <!-- Right side: Actions -->
    <div class="flex items-center gap-2">
        <a href="{{ route('home') }}" target="_blank" class="hidden md:flex text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-mango transition-colors items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            Storefront
        </a>

        <!-- Dark/Light Mode Toggle -->
        <button x-data="{ dark: localStorage.getItem('darkMode') === 'true' }" 
                x-init="$watch('dark', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val) }); if(dark) document.documentElement.classList.add('dark')"
                @click="dark = !dark"
                class="relative p-2 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none active:scale-95">
            <!-- Sun icon (shown in dark mode) -->
            <svg x-show="dark" x-cloak class="h-5 w-5 text-mango" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <!-- Moon icon (shown in light mode) -->
            <svg x-show="!dark" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <!-- Profile Dropdown -->
        <div class="ml-1 relative">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="flex items-center gap-2 p-1 border border-gray-100 dark:border-gray-700 rounded-full bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-colors shadow-sm">
                        <div class="w-8 h-8 bg-gray-900 rounded-full text-white flex items-center justify-center text-xs font-bold">{{ substr(Auth::user()->name, 0, 1) }}</div>
                        <div class="hidden md:block text-sm font-bold text-gray-700 dark:text-gray-300 pr-2">{{ Auth::user()->name }}</div>
                        <svg class="hidden md:block h-4 w-4 text-gray-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')" class="font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        {{ __('My Profile') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-500 font-bold hover:bg-red-50 flex items-center gap-2 border-t border-gray-50 mt-1 pt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            {{ __('Sign Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</div>
