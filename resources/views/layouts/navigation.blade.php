<aside class="fixed inset-y-0 left-0 z-50 transform transition-all duration-300 ease-in-out md:translate-x-0 md:static md:inset-0 flex flex-col" 
  :class="{
   'translate-x-0': sidebarOpen, 
   '-translate-x-full': !sidebarOpen,
   'w-[72px]': sidebarCollapsed,
   'w-[260px]': !sidebarCollapsed,
   'bg-gradient-to-b from-[#0F172A] via-[#131B2E] to-[#1E1B4B]': true
  }">
 
 <!-- Sidebar Header (Logo) -->
 <div class="h-[72px] flex items-center border-b border-white/5 shrink-0" :class="sidebarCollapsed ? 'px-3 justify-center' : 'px-5'">
  <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group" :class="sidebarCollapsed ? 'justify-center' : 'w-full'">
   <div class="w-10 h-10 gradient-bg-vibrant rounded-xl flex items-center justify-center transform group-hover:scale-105 transition-transform shadow-btn shrink-0">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
   </div>
   <h1 x-show="!sidebarCollapsed" x-transition.opacity class="text-lg font-black tracking-tight text-white whitespace-nowrap">Mission <span class="gradient-text">Control</span></h1>
  </a>
  <button @click="sidebarOpen = false" x-show="!sidebarCollapsed" class="md:hidden text-white/40 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10 ml-auto">
   <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
  </button>
 </div>

 <!-- Sidebar Navigation Links -->
 <div class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar py-6 space-y-6" :class="sidebarCollapsed ? 'px-2' : 'px-3'">
  
  <!-- Main Section -->
  <div>
   <div x-show="!sidebarCollapsed" class="text-[10px] font-black text-white/30 uppercase tracking-[0.15em] mb-3 px-3">Overview</div>
   <div class="space-y-1">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white shadow-sm' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Dashboard">
     @if(request()->routeIs('dashboard'))
     <div class="absolute left-0 w-1 h-6 gradient-bg-vibrant rounded-r-full" :class="sidebarCollapsed ? 'hidden' : ''"></div>
     @endif
     <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
     <span x-show="!sidebarCollapsed" x-transition.opacity>Dashboard</span>
    </a>
    @if(auth()->user()->hasPermission('analytics'))
     <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('analytics.*') ? 'bg-white/10 text-white shadow-sm' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Analytics">
      <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('analytics.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Analytics</span>
     </a>
    @endif
   </div>
  </div>

  @if(auth()->user()->hasPermission('orders') || auth()->user()->hasPermission('products') || auth()->user()->hasPermission('categories') || auth()->user()->hasPermission('customers'))
   <!-- E-Commerce Section -->
   <div>
    <div x-show="!sidebarCollapsed" class="text-[10px] font-black text-white/30 uppercase tracking-[0.15em] mb-3 px-3">E-Commerce</div>
    <div class="space-y-1">
     @if(auth()->user()->hasPermission('orders'))
     <a href="{{ route('orders.index') }}" class="flex items-center justify-between py-2.5 rounded-xl font-semibold text-sm transition-all relative {{ request()->routeIs('orders.*') && !request()->routeIs('orders.bulkBatches') ? 'bg-white/10 text-white' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Orders">
      @if(request()->routeIs('orders.*') && !request()->routeIs('orders.bulkBatches'))
      <div class="absolute left-0 w-1 h-6 gradient-bg-vibrant rounded-r-full" :class="sidebarCollapsed ? 'hidden' : ''"></div>
      @endif
      <div class="flex items-center gap-3">
       <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('orders.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
       <span x-show="!sidebarCollapsed" x-transition.opacity>Orders</span>
      </div>
      @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
       <span x-show="!sidebarCollapsed" class="gradient-bg-vibrant text-white py-0.5 px-2 rounded-full text-[10px] font-black shadow-btn">{{ $pendingOrdersCount }}</span>
      @endif
     </a>
     @if(Route::has('orders.bulkBatches'))
     <a href="{{ route('orders.bulkBatches') }}" class="flex items-center gap-3 py-2 rounded-xl font-semibold text-xs transition-all {{ request()->routeIs('orders.bulkBatches') ? 'bg-white/10 text-white' : 'text-white/30 hover:bg-white/5 hover:text-white/60' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3 ml-8'" title="Bulk Upload History">
      <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('orders.bulkBatches') ? 'text-primary-light' : 'text-white/20' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Upload History</span>
     </a>
     @endif
     @if(Route::has('orders.bulkShipments'))
     <a href="{{ route('orders.bulkShipments') }}" class="flex items-center gap-3 py-2 rounded-xl font-semibold text-xs transition-all {{ request()->routeIs('orders.bulkShipments') ? 'bg-white/10 text-white' : 'text-white/30 hover:bg-white/5 hover:text-white/60' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3 ml-8'" title="Bulk Shipments">
      <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('orders.bulkShipments') ? 'text-primary-light' : 'text-white/20' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Shipments History</span>
     </a>
     @endif
     @endif
     @if(auth()->user()->hasPermission('products'))
     <a href="{{ route('products.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all relative {{ request()->routeIs('products.*') ? 'bg-white/10 text-white' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Products">
      @if(request()->routeIs('products.*'))
      <div class="absolute left-0 w-1 h-6 gradient-bg-vibrant rounded-r-full" :class="sidebarCollapsed ? 'hidden' : ''"></div>
      @endif
      <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('products.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Products</span>
     </a>
     <a href="{{ route('flash-sales.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all relative {{ request()->routeIs('flash-sales.*') ? 'bg-white/10 text-white' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Flash Sales">
      <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('flash-sales.*') ? 'text-amber-400' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Flash Sales</span>
     </a>
     @endif
     @if(auth()->user()->hasPermission('categories'))
     <a href="{{ route('categories.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('categories.*') ? 'bg-white/10 text-white' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Categories">
      <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('categories.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Categories</span>
     </a>
     @endif
     @if(auth()->user()->hasPermission('customers'))
     <a href="{{ route('customers.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('customers.*') ? 'bg-white/10 text-white' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Customers">
      <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('customers.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Customers</span>
     </a>
     @endif
    </div>
   </div>

   @if(auth()->user()->hasPermission('facebook_inbox'))
   <!-- Marketing Section -->
   <div>
    <div x-show="!sidebarCollapsed" class="text-[10px] font-black text-white/30 uppercase tracking-[0.15em] mb-3 px-3">Marketing</div>
    <div class="space-y-1">
     <a href="{{ route('facebook-inbox.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('facebook-inbox.*') ? 'bg-blue-500/15 text-blue-400' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Facebook Inbox">
      <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('facebook-inbox.*') ? 'text-blue-400' : 'text-white/30' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Facebook Inbox</span>
     </a>
     <a href="{{ route('ai-agent.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('ai-agent.*') ? 'bg-purple-500/15 text-purple-400' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="AI Agent">
      <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('ai-agent.*') ? 'text-purple-400' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>AI Agent</span>
     </a>
     <a href="{{ route('support-tickets.index') }}" class="flex items-center justify-between py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('support-tickets.*') ? 'bg-orange-500/15 text-orange-400' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Support Tickets">
      <div class="flex items-center gap-3">
       <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('support-tickets.*') ? 'text-orange-400' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
       <span x-show="!sidebarCollapsed" x-transition.opacity>Support Tickets</span>
      </div>
      @php
       $openTickets = \App\Models\SupportTicket::where('status', 'open')->count();
      @endphp
      @if($openTickets > 0)
       <span x-show="!sidebarCollapsed" class="bg-red-500 text-white py-0.5 px-2 rounded-full text-[10px] font-black">{{ $openTickets }}</span>
      @endif
     </a>
    </div>
   </div>
   @endif

   @if(auth()->user()->hasPermission('pathao'))
   <!-- Fulfillment Section -->
   <div>
    <div x-show="!sidebarCollapsed" class="text-[10px] font-black text-white/30 uppercase tracking-[0.15em] mb-3 px-3">Fulfillment</div>
    <div class="space-y-1">
     <a href="{{ route('pathao.index') }}" class="flex items-center gap-3 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('pathao.*') ? 'bg-cyan-500/15 text-cyan-400' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Pathao Manager">
      <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('pathao.*') ? 'text-cyan-400' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
      <span x-show="!sidebarCollapsed" x-transition.opacity>Pathao Manager</span>
     </a>
     <a href="{{ route('rider_comments.index') }}" class="flex items-center justify-between py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('rider_comments.*') ? 'bg-white/10 text-white' : 'text-white/50 hover:bg-white/5 hover:text-white/80' }}" :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'" title="Rider Comments">
      <div class="flex items-center gap-3">
       <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('rider_comments.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
       <span x-show="!sidebarCollapsed" x-transition.opacity>Rider Comments</span>
      </div>
      @php
       $unreadComments = \App\Models\RiderComment::where('status', 'unread')->count();
      @endphp
      @if($unreadComments > 0)
       <span x-show="!sidebarCollapsed" class="bg-red-500 text-white py-0.5 px-2 rounded-full text-[10px] font-black">{{ $unreadComments }}</span>
      @endif
     </a>
    </div>
   </div>
   @endif
  @endif

  @if(auth()->user()->hasPermission('accounting') || auth()->user()->hasPermission('purchases') || auth()->user()->hasPermission('expenses'))
   <!-- Finance Section -->
   <div x-data="{ open: {{ request()->routeIs('accounting.*') || request()->routeIs('purchases.*') || request()->routeIs('expenses.*') ? 'true' : 'false' }} }">
    <template x-if="!sidebarCollapsed">
     <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold text-sm transition-all text-white/50 hover:bg-white/5 hover:text-white/80 group">
      <div class="flex items-center gap-3">
       <svg class="w-5 h-5 text-white/30 group-hover:text-white/50 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
       Finance
      </div>
      <svg class="w-4 h-4 transition-transform transform text-white/20" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
     </button>
    </template>
    <template x-if="sidebarCollapsed">
     <div class="text-[10px] font-black text-white/20 uppercase tracking-widest mb-2 text-center">💰</div>
    </template>
    <div x-show="open || sidebarCollapsed" x-collapse class="space-y-1" :class="sidebarCollapsed ? '' : 'pl-11 pr-3 pt-2'">
     @if(auth()->user()->hasPermission('accounting'))
     <a href="{{ route('accounting.index') }}" class="block py-2 text-sm font-semibold transition-colors {{ request()->routeIs('accounting.*') ? 'text-primary-light' : 'text-white/30 hover:text-white/70' }}" :class="sidebarCollapsed ? 'text-center text-[10px]' : ''" title="Accounting">
      <span x-show="!sidebarCollapsed">Overview</span>
      <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('accounting.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
     </a>
     @endif
     @if(auth()->user()->hasPermission('purchases'))
     <a href="{{ route('purchases.index') }}" class="block py-2 text-sm font-semibold transition-colors {{ request()->routeIs('purchases.*') ? 'text-primary-light' : 'text-white/30 hover:text-white/70' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Purchases">
      <span x-show="!sidebarCollapsed">Purchases</span>
      <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('purchases.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
     </a>
     @endif
     @if(auth()->user()->hasPermission('expenses'))
     <a href="{{ route('expenses.index') }}" class="block py-2 text-sm font-semibold transition-colors {{ request()->routeIs('expenses.*') ? 'text-primary-light' : 'text-white/30 hover:text-white/70' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Expenses">
      <span x-show="!sidebarCollapsed">Expenses</span>
      <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('expenses.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
     </a>
     @endif
    </div>
   </div>
  @endif

  @if(auth()->user()->hasPermission('settings') || auth()->user()->hasPermission('users'))
   <!-- Administration -->
   <div x-data="{ open: {{ request()->routeIs('settings.*') || request()->routeIs('users.*') ? 'true' : 'false' }} }">
    <template x-if="!sidebarCollapsed">
     <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold text-sm transition-all text-white/50 hover:bg-white/5 hover:text-white/80 group">
      <div class="flex items-center gap-3">
       <svg class="w-5 h-5 text-white/30 group-hover:text-white/50 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
       Settings
      </div>
      <svg class="w-4 h-4 transition-transform transform text-white/20" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
     </button>
    </template>
    <template x-if="sidebarCollapsed">
     <div class="text-[10px] font-black text-white/20 uppercase tracking-widest mb-2 text-center">⚙️</div>
    </template>
    <div x-show="open || sidebarCollapsed" x-collapse class="space-y-1" :class="sidebarCollapsed ? '' : 'pl-11 pr-3 pt-2'">
     @if(auth()->user()->hasPermission('settings'))
     <a href="{{ route('settings.index', ['tab' => 'frontend']) }}" class="block py-2 text-sm font-semibold transition-colors {{ request()->routeIs('settings.*') ? 'text-primary-light' : 'text-white/30 hover:text-white/70' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Settings">
      <span x-show="!sidebarCollapsed">General Settings</span>
      <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('settings.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
     </a>
     <a href="{{ route('activity-log.index') }}" class="block py-2 text-sm font-semibold transition-colors {{ request()->routeIs('activity-log.*') ? 'text-primary-light' : 'text-white/30 hover:text-white/70' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Activity Log">
      <span x-show="!sidebarCollapsed">Activity Log</span>
      <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('activity-log.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
     </a>
     @endif
     @if(auth()->user()->hasPermission('users'))
     <a href="{{ route('users.index') }}" class="block py-2 text-sm font-semibold transition-colors {{ request()->routeIs('users.*') ? 'text-primary-light' : 'text-white/30 hover:text-white/70' }}" :class="sidebarCollapsed ? 'text-center' : ''" title="Staff & Roles">
      <span x-show="!sidebarCollapsed">Staff & Roles</span>
      <svg x-show="sidebarCollapsed" class="w-5 h-5 mx-auto {{ request()->routeIs('users.*') ? 'text-primary-light' : 'text-white/30' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
     </a>
     @endif
    </div>
   </div>
  @endif
  
 </div>

 <!-- Sidebar Footer -->
 <div class="border-t border-white/5" :class="sidebarCollapsed ? 'p-2' : 'p-3'">
  <div class="bg-white/5 rounded-2xl flex items-center" :class="sidebarCollapsed ? 'p-2 justify-center' : 'p-3 justify-between'">
   <div x-show="!sidebarCollapsed">
    <p class="text-xs font-semibold text-white/30">System Status</p>
    <p class="text-sm font-bold text-emerald-400 flex items-center gap-1.5 mt-0.5">
     <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
     All Systems Go
    </p>
   </div>
   <span x-show="sidebarCollapsed" class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse" title="All Systems Operational"></span>
  </div>
 </div>
</aside>
<!-- Mobile Overlay -->
<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden" @click="sidebarOpen = false"></div>
