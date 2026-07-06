<div x-data="topbarSearch()" @keydown.meta.k.window.prevent="$refs.globalSearch.focus()" @keydown.ctrl.k.window.prevent="$refs.globalSearch.focus()" class="bg-white/80 backdrop-blur-xl border-b border-gray-100/50 sticky top-0 z-40 h-[72px] flex items-center justify-between px-4 sm:px-6 lg:px-8">
 <!-- Left side: Sidebar Toggle & Search -->
 <div class="flex items-center gap-4 flex-1">
  <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-primary focus:outline-none transition-colors active:scale-95 p-2 rounded-xl hover:bg-primary/5 md:hidden">
   <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
   </svg>
  </button>

  <!-- Sidebar Collapse Toggle (desktop only) -->
  <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden md:flex text-gray-400 hover:text-primary focus:outline-none transition-all p-2 rounded-xl hover:bg-primary/5">
   <svg x-show="!sidebarCollapsed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
   <svg x-show="sidebarCollapsed" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
  </button>

  <!-- Global Search (PHASE-3: live results, permission-aware) -->
  <div class="hidden sm:flex items-center max-w-md w-full relative group" @click.outside="open = false">
   <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
    <svg class="h-4 w-4 text-gray-400 group-focus-within:text-primary transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
     <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
    </svg>
   </div>
   <input x-ref="globalSearch" type="text" x-model="query" @input.debounce.300ms="run()" @focus="query.length >= 2 && (open = true)"
       @keydown.escape="open = false; $refs.globalSearch.blur()"
       @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="go()"
       autocomplete="off"
       class="block w-full pl-10 pr-14 py-2.5 border border-gray-200/60 rounded-xl leading-5 bg-gray-50/80 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary/30 sm:text-sm transition-all font-medium" placeholder="Search orders, products, customers...">
   <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
    <kbd class="hidden sm:inline-flex items-center border border-gray-200/60 rounded-lg px-2 py-0.5 text-[10px] font-bold text-gray-400 bg-white/80 shadow-sm gap-0.5">Ctrl K</kbd>
   </div>

   <!-- Results dropdown -->
   <div x-show="open" x-cloak x-transition.opacity class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl border border-gray-100 shadow-2xl overflow-hidden max-h-[70vh] overflow-y-auto z-50">
    <template x-if="loading">
     <div class="px-4 py-3 text-sm font-bold text-gray-400 flex items-center gap-2">
      <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
      Searching…
     </div>
    </template>
    <template x-if="!loading && !flat.length && query.length >= 2">
     <div class="px-4 py-3 text-sm font-bold text-gray-400">No results for "<span x-text="query"></span>"</div>
    </template>
    <template x-for="group in groups" :key="group.label">
     <div>
      <div class="px-4 pt-3 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-wider" x-text="group.label"></div>
      <template x-for="item in group.items" :key="item.url + item.title">
       <a :href="item.url" @mouseenter="active = flat.indexOf(item)"
         :class="flat.indexOf(item) === active ? 'bg-indigo-50' : ''"
         class="block px-4 py-2 hover:bg-indigo-50 transition">
        <p class="text-sm font-bold text-gray-900" x-text="item.title"></p>
        <p class="text-xs font-medium text-gray-400" x-text="item.sub"></p>
       </a>
      </template>
     </div>
    </template>
   </div>
  </div>
 </div>

 <!-- Right side: Actions -->
 <div class="flex items-center gap-2">

  <!-- Notification Bell (PHASE-3.4) -->
  <div x-data="notificationBell()" x-init="load(); setInterval(() => load(), 60000)" class="relative" @click.outside="open = false">
   <button @click="toggle()" class="relative text-gray-400 hover:text-primary p-2 rounded-xl hover:bg-primary/5 transition-all" title="Notifications">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
    <span x-show="unread > 0" x-cloak class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px] font-black min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center shadow" x-text="unread > 9 ? '9+' : unread"></span>
   </button>

   <div x-show="open" x-cloak x-transition.opacity class="absolute right-0 top-full mt-2 w-96 max-w-[90vw] bg-white rounded-2xl border border-gray-100 shadow-2xl overflow-hidden z-50">
    <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between">
     <h4 class="font-black text-sm text-gray-900">Notifications</h4>
     <span class="text-[10px] font-bold text-gray-400" x-show="items.length" x-text="items.length + ' recent'"></span>
    </div>
    <div class="max-h-[60vh] overflow-y-auto divide-y divide-gray-50">
     <template x-if="!items.length">
      <p class="px-4 py-8 text-center text-sm font-bold text-gray-300">All caught up 🎉</p>
     </template>
     <template x-for="(item, i) in items" :key="i">
      <a :href="item.url" class="block px-4 py-3 hover:bg-gray-50 transition" :class="item.unread ? 'bg-indigo-50/40' : ''">
       <div class="flex items-start justify-between gap-2">
        <p class="text-sm font-bold text-gray-900" x-text="item.title"></p>
        <span class="text-[10px] font-bold text-gray-300 shrink-0" x-text="item.time_human"></span>
       </div>
       <p class="text-xs font-medium text-gray-400 mt-0.5" x-text="item.sub"></p>
      </a>
     </template>
    </div>
   </div>
  </div>

  <!-- View Storefront -->
  <a href="{{ route('home') }}" target="_blank" class="hidden md:flex text-sm font-bold text-gray-500 hover:text-primary transition-all items-center gap-1.5 px-3 py-2 rounded-xl hover:bg-primary/5">
   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
   Storefront
  </a>

  <!-- Profile Dropdown -->
  <div class="ml-1 relative">
   <x-dropdown align="right" width="48">
    <x-slot name="trigger">
     <button class="flex items-center gap-2.5 p-1.5 border border-gray-100/50 rounded-2xl bg-gray-50/60 hover:bg-primary/5 focus:outline-none transition-all hover:border-primary/20 group">
      <div class="w-8 h-8 gradient-bg-vibrant rounded-xl text-white flex items-center justify-center text-xs font-bold shadow-btn group-hover:shadow-glow transition-shadow">{{ substr(Auth::user()->name, 0, 1) }}</div>
      <div class="hidden md:block text-sm font-bold text-gray-700 group-hover:text-primary transition-colors">{{ Auth::user()->name }}</div>
      <svg class="hidden md:block h-4 w-4 text-gray-400 mr-1 group-hover:text-primary transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
       <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
     </button>
    </x-slot>

    <x-slot name="content">
     <x-dropdown-link :href="route('profile.edit')" class="font-bold flex items-center gap-2">
      <svg class="w-4 h-4 text-primary/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
      {{ __('My Profile') }}
     </x-dropdown-link>

     <form method="POST" action="{{ route('logout') }}">
      @csrf
      <x-dropdown-link :href="route('logout')"
        onclick="event.preventDefault(); this.closest('form').submit();" class="text-accent-rose font-bold hover:bg-red-50 flex items-center gap-2 border-t border-gray-50 mt-1 pt-1">
       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
       {{ __('Sign Out') }}
      </x-dropdown-link>
     </form>
    </x-slot>
   </x-dropdown>
  </div>
 </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('topbarSearch', () => ({
            query: '',
            groups: [],
            open: false,
            loading: false,
            active: -1,

            get flat() {
                return this.groups.flatMap(g => g.items);
            },

            async run() {
                const q = this.query.trim();
                // Mirror the backend rule: min 2 chars unless it's a number (order id)
                if (q.length < 2 && !/^\d+$/.test(q)) {
                    this.groups = [];
                    this.open = false;
                    return;
                }
                this.loading = true;
                this.open = true;
                try {
                    const resp = await fetch('{{ route('api.globalSearch') }}?q=' + encodeURIComponent(this.query), {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await resp.json();
                    this.groups = data.groups || [];
                    this.active = this.flat.length ? 0 : -1;
                } catch (e) {
                    this.groups = [];
                } finally {
                    this.loading = false;
                }
            },

            move(dir) {
                if (!this.flat.length) return;
                this.active = (this.active + dir + this.flat.length) % this.flat.length;
            },

            go() {
                const item = this.flat[this.active];
                if (item) window.location = item.url;
            },
        }));

        // PHASE-3.4: notification bell
        Alpine.data('notificationBell', () => ({
            items: [],
            unread: 0,
            open: false,

            async load() {
                try {
                    const resp = await fetch('{{ route('api.notifications') }}', { headers: { Accept: 'application/json' } });
                    const data = await resp.json();
                    this.items = data.items || [];
                    this.unread = data.unread || 0;
                } catch (e) { /* topbar must never break the page */ }
            },

            async toggle() {
                this.open = !this.open;
                if (this.open && this.unread > 0) {
                    this.unread = 0;
                    try {
                        await fetch('{{ route('api.notifications.seen') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                    } catch (e) { /* ignore */ }
                }
            },
        }));
    });
</script>
