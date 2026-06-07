<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ setting('store_name', 'Chhito Pasal') }} | Premium Store</title>
  <meta name="description" content="{{ setting('meta_description', 'Discover the best tech, fashion, and home accessories at Chhito Pasal. Fast delivery across Nepal.') }}">
  <meta property="og:title" content="{{ setting('store_name', 'Chhito Pasal') }} | Premium Store">
  <meta property="og:description" content="{{ setting('meta_description', 'Discover the best tech, fashion, and home accessories at Chhito Pasal. Fast delivery across Nepal.') }}">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url('/') }}">
  <link rel="canonical" href="{{ url('/') }}">
  
  <!-- PWA / Mobile App Meta Tags -->
  <meta name="theme-color" content="#FF4C4C">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  @if(setting('store_favicon'))
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('store_favicon')) }}">
  @else
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">
  @endif

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  {{-- Alpine.js is already bundled via app.js — do NOT load the CDN version too --}}

  <style>
    /* Desktop-only hover effects */
    @media (min-width: 768px) {
      .card-shimmer::after {
        content: '';
        position: absolute;
        top: 0; left: -100%; width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        z-index: 5;
        pointer-events: none;
      }
      .group:hover .card-shimmer::after {
        animation: shimmerSweep 0.8s ease forwards;
      }
      @keyframes shimmerSweep { to { left: 130%; } }
      .card-actions-desktop {
        opacity: 0;
        transform: translateY(12px);
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
      }
      .group:hover .card-actions-desktop {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>

  {{-- Google Tag Manager --}}
  @if(setting('google_tag_manager_id'))
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','{{ setting("google_tag_manager_id") }}');</script>
  @endif

  {{-- Facebook Pixel --}}
  @if(setting('facebook_pixel_id'))
  <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
    (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ setting("facebook_pixel_id") }}');
    fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ setting('facebook_pixel_id') }}&ev=PageView&noscript=1"/></noscript>
  @endif
</head>
<body class="antialiased bg-gray-50 text-gray-800 font-sans pb-20 md:pb-0" x-data="shopData()">

  <!-- Toast Notification -->
  <div x-show="toastVisible" x-cloak 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform translate-y-2"
     class="fixed bottom-24 left-1/2 -translate-x-1/2 z-[100] bg-gray-900 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 text-sm font-medium w-max max-w-[90vw]">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <span x-text="toastMessage" class="truncate"></span>
  </div>
  {{-- Google Tag Manager (noscript) --}}
  @if(setting('google_tag_manager_id'))
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ setting('google_tag_manager_id') }}"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  @endif

  <!-- 1. Header -->
  <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 hidden md:block bg-white/90 backdrop-blur-md border-b border-gray-100" :class="scrolled ? 'py-2 shadow-sm' : 'py-4'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
      
      <!-- Brand -->
      <a href="{{ route('home') }}" class="flex items-center gap-2">
        @if(setting('store_logo'))
          <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-8 md:h-10 w-auto object-contain">
        @endif
        <span class="text-xl md:text-2xl font-bold font-display tracking-tight text-gray-900">
          {{ setting('store_name', 'Chhito Pasal') }}
        </span>
      </a>

      <!-- Desktop Nav -->
      <nav class="hidden md:flex items-center gap-8 font-medium">
        <a href="{{ route('home') }}" class="transition-colors text-sm font-bold text-gray-900 hover:text-red-600">Home</a>
        <a href="{{ route('shop') }}" class="transition-colors text-sm text-gray-600 hover:text-red-600">Shop</a>
        <a href="{{ route('company.profile') }}" class="transition-colors text-sm text-gray-600 hover:text-red-600">Company</a>
      </nav>

      <!-- Actions -->
      <div class="flex items-center gap-4">
        <div class="relative hidden md:block">
          <input type="text" x-model="searchQuery" placeholder="Search products..." class="border-gray-200 rounded-full py-2 pl-10 pr-4 focus:bg-white focus:ring-1 focus:ring-gray-900 focus:border-gray-900 transition-all text-sm w-64 bg-gray-50 text-gray-900 placeholder-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-4 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        </div>

        <button @click="toggleCart()" class="relative transition-colors text-gray-800 hover:text-red-600 p-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
          <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" x-transition class="absolute top-0 right-0 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center bg-gray-900 border border-white"></span>
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile App Header -->
  <header class="md:hidden fixed top-0 left-0 right-0 z-50 bg-white shadow-sm pb-3 pt-safe border-b border-gray-100">
    <div class="px-4 py-2 flex items-center justify-between">
      <a href="{{ route('home') }}" class="flex items-center gap-2">
        @if(setting('store_logo'))
          <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-7 w-auto object-contain">
        @else
          <span class="text-xl font-bold font-display text-gray-900 tracking-tight">
            {{ setting('store_name', 'Chhito Pasal') }}
          </span>
        @endif
      </a>
      <div class="flex items-center gap-3">
        <button @click="toggleCart()" class="relative text-gray-800 p-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
          <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" class="absolute top-0 right-0 w-4 h-4 bg-gray-900 text-white rounded-full border border-white text-[9px] font-black flex items-center justify-center"></span>
        </button>
      </div>
    </div>
    <!-- Persistent Search Bar -->
    <div class="px-4 mt-1">
      <div class="relative">
        <input type="text" x-model="searchQuery" placeholder="Search for products..." class="w-full border-gray-200 rounded-full py-2 pl-10 pr-4 bg-gray-50 text-gray-900 focus:ring-1 focus:ring-gray-900 focus:border-gray-900 text-sm placeholder-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
      </div>
    </div>
  </header>

  <!-- 2. Hero Section / Flash Sale Banner -->
  <section class="relative z-20 md:mt-20 pt-[80px] md:pt-0">
    @if(setting('home_banner_desktop') || setting('home_banner_mobile'))
      <a href="{{ route('frontend.flash-sales') }}" class="block w-full">
        @if(setting('home_banner_desktop'))
          <img src="{{ asset('storage/' . setting('home_banner_desktop')) }}" alt="Flash Sale" class="w-full h-auto hidden md:block object-cover">
        @endif
        @if(setting('home_banner_mobile'))
          <img src="{{ asset('storage/' . setting('home_banner_mobile')) }}" alt="Flash Sale" class="w-full h-auto block md:hidden object-cover">
        @endif
      </a>
    @else
      <div class="bg-gray-900 border-y border-gray-800 p-8 sm:p-12 md:p-24 text-white flex flex-col md:flex-row items-center justify-center gap-8 relative overflow-hidden min-h-[400px]">
        <!-- Subtle elegant pattern/texture -->
        <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] mix-blend-overlay pointer-events-none"></div>
        
        <div class="flex-1 relative z-10 text-center flex flex-col items-center">
          <div class="flex items-center gap-4 mb-4">
            <div class="h-[1px] w-12 bg-red-500"></div>
            <span class="text-red-400 text-xs font-bold uppercase tracking-[0.2em]">Exclusive Event</span>
            <div class="h-[1px] w-12 bg-red-500"></div>
          </div>
          <h2 class="text-4xl sm:text-6xl md:text-7xl font-display font-light tracking-tight mb-6">Flash <span class="font-bold text-white">Sales</span></h2>
          <p class="text-gray-400 text-sm sm:text-base md:text-lg font-light max-w-2xl leading-relaxed mb-8">Curated selections at exceptional value. Discover premium items available for a limited time.</p>
        </div>
      </div>
    @endif
  </section>

  <!-- Flash Sales Grid -->
  @if(isset($flashSaleProducts) && $flashSaleProducts->count() > 0)
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-16 relative z-20">
    <div class="flex justify-between items-end mb-6 border-b border-gray-200 pb-4">
      <h2 class="text-2xl font-display font-bold text-gray-900 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Flash Sales
      </h2>
      <a href="{{ route('frontend.flash-sales') }}" class="text-sm font-medium text-gray-500 hover:text-red-600 transition-colors">See All</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6 lg:gap-8 stagger-grid">
      @foreach($flashSaleProducts->take(4) as $product)
        <div class="bg-white border border-gray-100 rounded-lg md:rounded-2xl shadow-sm hover:shadow-xl hover:border-gray-200 transition-all duration-300 transform hover:-translate-y-1 relative group cursor-pointer overflow-hidden flex flex-col" @click="window.location.href = '{{ url('product') }}/{{ $product->parent_product_slug ?: $product->slug }}{{ isset($product->bundle_qty) ? '?bundle=' . $product->bundle_qty : '' }}'">
          <div class="absolute top-2 left-2 md:top-4 md:left-4 z-20">
            <span class="bg-gray-900 text-white text-[9px] md:text-[10px] font-bold uppercase tracking-widest px-2 py-1 md:px-3 rounded-full flex flex-col items-center justify-center leading-none">Limited</span>
          </div>
          
          <div class="relative bg-gray-50 overflow-hidden w-full aspect-[4/5] flex items-center justify-center p-4 transition-all duration-500">
            <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-700 ease-in-out">
          </div>
          
          <div class="p-4 flex flex-col flex-grow relative bg-white border-t border-gray-50 text-center">
            <h3 class="font-display font-medium text-gray-900 text-[13px] md:text-sm line-clamp-1 mb-1">{{ $product->name }}</h3>
            <div class="mt-auto flex justify-center items-center gap-2">
              <span class="text-gray-400 font-medium text-xs line-through">Rs.{{ number_format($product->original_price) }}</span>
              <span class="text-red-600 font-bold text-sm md:text-base">Rs.{{ number_format($product->price) }}</span>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </section>
  @endif

  <!-- 5. Product Grid (New Arrivals) -->
  <main id="shop" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-24">
    
    <div class="flex justify-between items-end mb-8 border-b border-gray-200 pb-4">
      <h2 class="text-2xl font-display font-bold text-gray-900">New Arrival</h2>
      <a href="{{ route('shop') }}" class="text-sm font-medium text-gray-500 hover:text-red-600 transition-colors">See All</a>
    </div>

    <!-- Empty State -->
    <div x-show="filteredProducts.length === 0" x-cloak class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
      <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
      </div>
      <h3 class="text-lg font-display font-bold text-gray-900 mb-1">No Products Found</h3>
      <p class="text-gray-500 text-sm mb-4">We couldn't find anything matching your criteria.</p>
      <button @click="searchQuery = ''; activeCategory = 'all'" class="text-red-600 font-bold text-sm hover:underline">Clear Filters</button>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6 lg:gap-8 stagger-grid">
      <template x-for="(product, index) in filteredProducts" :key="product.id + '_' + (product.bundle_qty || 0)">
        <!-- Outer Card Container -->
        <article class="bg-white border border-gray-100 rounded-lg md:rounded-2xl shadow-sm hover:shadow-xl hover:border-gray-200 transition-all duration-300 transform hover:-translate-y-1 flex flex-col group cursor-pointer relative overflow-hidden" @click="window.location.href = '{{ url('product') }}/' + (product.parent_product_slug || product.slug) + (product.bundle_qty ? '?bundle=' + product.bundle_qty : '')">
          
          <!-- Badge (Simulated New/Hot) -->
          <div x-show="index % 3 === 0" class="absolute top-2 right-2 md:top-4 md:right-4 z-20">
            <span class="bg-gray-900 text-white text-[9px] md:text-[10px] font-bold uppercase tracking-widest px-2 py-1 md:px-3 rounded-full flex flex-col items-center justify-center leading-none">
              <span>New</span>
            </span>
          </div>
          <div x-show="index % 5 === 0" class="absolute top-2 left-2 md:top-4 md:left-4 z-20">
            <span class="bg-red-600 text-white text-[9px] md:text-[10px] font-bold uppercase tracking-widest px-2 py-1 md:px-3 rounded-full flex flex-col items-center justify-center leading-none">
              <span>-20%</span>
            </span>
          </div>
          <!-- Bundle Only Pack Badge -->
          <div x-show="product.is_bundle_card" class="absolute top-2 left-2 md:top-4 md:left-4 z-20">
            <span class="bg-amber-500 text-white text-[9px] md:text-[10px] font-bold uppercase tracking-widest px-2 py-1 md:px-3 rounded-full flex items-center gap-1 leading-none">
              <span x-text="'Pack of ' + product.bundle_qty"></span>
            </span>
          </div>
          
          <!-- Inner Image Container -->
          <div class="relative bg-gray-50 overflow-hidden w-full aspect-[4/5] flex items-center justify-center p-4 transition-all duration-500">
            <img :src="'{{ asset('storage') }}/' + product.image_path" :alt="product.name" class="w-full h-full object-contain mix-blend-multiply transition-transform duration-700 group-hover:scale-105" loading="lazy" x-on:error="$el.src='https://via.placeholder.com/300?text=No+Image'">
            
            <!-- Hover Action Button inside image area -->
            <div class="hidden md:flex absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10 items-center justify-center backdrop-blur-sm">
              <button @click.stop.prevent="triggerAddToCart(product)" class="bg-gray-900 text-white text-sm font-bold py-3 px-6 rounded-full transform translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center gap-2 hover:bg-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Quick Add
              </button>
            </div>
          </div>
          
          <!-- Text Content -->
          <div class="p-4 flex flex-col flex-grow relative bg-white border-t border-gray-50">
            <div class="w-full flex flex-col mb-4 md:items-center text-center">
              <h3 class="font-display font-medium text-gray-900 text-[13px] md:text-sm line-clamp-1 mb-1" x-text="product.name"></h3>
              <p class="text-gray-400 text-[10px] md:text-xs font-medium uppercase tracking-widest line-clamp-1" x-text="product.category ? product.category.name : 'Accessories'"></p>
            </div>
            <div class="mt-auto flex justify-between md:justify-center items-center">
              <template x-if="product.is_flash_sale">
                <div class="flex items-center gap-2">
                  <span class="text-gray-400 font-medium text-xs line-through" x-text="'Rs.' + product.original_price.toLocaleString()"></span>
                  <span class="text-red-600 font-bold text-sm md:text-base" x-text="'Rs.' + product.price.toLocaleString()"></span>
                </div>
              </template>
              <template x-if="!product.is_flash_sale">
                <span class="text-gray-900 font-bold text-sm md:text-base" x-text="'Rs.' + product.price.toLocaleString()"></span>
              </template>
              <!-- Mobile Quick Add Button -->
              <button @click.stop.prevent="triggerAddToCart(product)" class="md:hidden w-8 h-8 rounded-full bg-gray-100 text-gray-900 flex items-center justify-center hover:bg-gray-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
              </button>
            </div>
          </div>
        </article>
      </template>
    </div>
  </main>

  <!-- Categories Scroller -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-8">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-display font-bold text-gray-900">Shop by Category</h2>
    </div>
    <div class="flex gap-3 overflow-x-auto no-scrollbar pb-2">
      <!-- All -->
      <button @click="activeCategory = 'all'" 
          class="px-6 py-2.5 rounded-full text-sm font-medium whitespace-nowrap transition-all border"
          :class="activeCategory === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-900'">
        All Categories
      </button>
      
      @foreach($categories as $category)
        <button @click="activeCategory = '{{ $category->slug }}'" 
            class="px-6 py-2.5 rounded-full text-sm font-medium whitespace-nowrap transition-all border"
            :class="activeCategory === '{{ $category->slug }}' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-900'">
          {{ $category->name }}
        </button>
      @endforeach
    </div>
  </section>


  <!-- 6. Footer -->
  <footer id="about" class="hidden md:block bg-white border-t border-gray-100 pt-10 md:pt-16 pb-24 md:pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Desktop Footer Layout -->
      <div class="hidden md:grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
        <div class="col-span-1 md:col-span-2">
          <a href="{{ route('home') }}" class="flex items-center gap-2 mb-4">
            @if(setting('store_logo'))
              <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-8 w-auto object-contain">
            @endif
            <span class="text-xl font-bold font-display text-gray-900">
              {{ setting('store_name', 'Chhito Pasal') }}
            </span>
          </a>
          <p class="text-gray-500 max-w-md mb-6 leading-relaxed text-sm">
            {{ setting('hero_subtitle', 'Discover the best tech, fashion, and home accessories delivered straight to your door.') }}
          </p>
        </div>
        <div>
          <h4 class="font-display font-bold text-gray-900 mb-4">Quick Links</h4>
          <ul class="space-y-3 text-gray-500 text-sm">
            <li><a href="#" class="hover:text-primary transition-colors">Home</a></li>
            <li><a href="#shop" class="hover:text-primary transition-colors">Shop</a></li>
            <li><a href="{{ route('privacy.policy') }}" class="hover:text-primary transition-colors">Privacy Policy</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-display font-bold text-gray-900 mb-4">Company</h4>
          <ul class="space-y-3 text-gray-500 text-sm">
            <li><a href="{{ route('company.profile') }}" class="hover:text-primary transition-colors">About Us</a></li>
            <li><a href="tel:{{ setting('order_contact_number') }}" class="hover:text-primary transition-colors">Contact</a></li>
            <li><a href="{{ route('login') }}" class="hover:text-primary transition-colors">Admin Login</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-display font-bold text-gray-900 mb-4">Contact Us</h4>
          <ul class="space-y-3 text-gray-500 text-sm">
            <li><a href="mailto:{{ setting('contact_email', 'support@chhitopasal.com') }}" class="hover:text-primary transition-colors">{{ setting('contact_email', 'support@chhitopasal.com') }}</a></li>
            <li><a href="tel:{{ setting('contact_phone', '+977 9800000000') }}" class="hover:text-primary transition-colors">{{ setting('contact_phone', '+977 9800000000') }}</a></li>
            <li><span class="cursor-default">{{ setting('contact_address', 'Kathmandu, Nepal') }}</span></li>
          </ul>
        </div>
      </div>

      <!-- Mobile App-like Footer -->
      <div class="md:hidden flex flex-col items-center justify-center text-center pb-2">
        @if(setting('store_logo'))
          <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-8 w-auto object-contain mb-4 grayscale opacity-50">
        @endif
        
        <div class="flex gap-4 text-[11px] font-bold text-gray-400 mb-4 uppercase tracking-wider">
          <a href="#" class="hover:text-primary transition-colors">Terms</a>
          <span>&bull;</span>
          <a href="{{ route('privacy.policy') }}" class="hover:text-primary transition-colors">Privacy</a>
          <span>&bull;</span>
          <a href="#" class="hover:text-primary transition-colors">Help</a>
        </div>
        
        <p class="text-[10px] text-gray-300 font-bold uppercase tracking-[0.2em]">
          App Version 1.0.4
        </p>
        <p class="text-[9px] text-gray-300 mt-2 font-medium">
          &copy; {{ date('Y') }} {{ setting('store_name', 'Chhito Pasal') }}
        </p>
      </div>

      <!-- Desktop Copyright -->
      <div class="hidden md:flex border-t border-gray-100 pt-8 flex-col md:flex-row items-center justify-between text-gray-400 text-sm">
        <p>&copy; {{ date('Y') }} {{ setting('store_name', 'Chhito Pasal') }}. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- 7. Product Quick View Modal -->
  <div x-show="quickViewOpen" x-cloak class="fixed inset-0 z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div x-show="quickViewOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="closeQuickView()"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="quickViewOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl flex flex-col md:flex-row">
          
          <!-- Close Button -->
          <button @click="closeQuickView()" class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/80 backdrop-blur rounded-full flex items-center justify-center text-gray-500 hover:bg-primary hover:text-white transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>

          <!-- Modal Content -->
          <template x-if="selectedProduct">
            <div class="flex flex-col md:flex-row w-full">
              <!-- Image Half (Gallery) -->
              <div class="w-full md:w-1/2 bg-gray-50 p-6 sm:p-10 flex flex-col items-center justify-center relative">
                <div class="w-full aspect-[4/5] bg-white rounded-xl overflow-hidden flex items-center justify-center mb-4 relative z-10">
                  <template x-if="!quickViewIsVideo">
                    <img :src="quickViewMedia" :alt="selectedProduct.name" class="w-full h-full object-contain p-4">
                  </template>
                  <template x-if="quickViewIsVideo">
                    <video :src="quickViewMedia" controls autoplay muted class="w-full h-full object-contain bg-black"></video>
                  </template>
                </div>

                <!-- Thumbnails -->
                <div class="flex gap-3 overflow-x-auto no-scrollbar w-full py-2 relative z-10">
                  <button @click="quickViewMedia = '{{ asset('storage') }}/' + selectedProduct.image_path; quickViewIsVideo = false" 
                      :class="quickViewMedia === '{{ asset('storage') }}/' + selectedProduct.image_path ? 'border-primary ring-2 ring-primary/30' : 'border-gray-200 opacity-70 hover:opacity-100 hover:border-gray-400'"
                      class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden border transition-all active:scale-95 bg-white">
                    <img :src="'{{ asset('storage') }}/' + selectedProduct.image_path" class="w-full h-full object-cover">
                  </button>

                  <template x-if="selectedProduct.additional_images && selectedProduct.additional_images.length > 0">
                    <template x-for="img in selectedProduct.additional_images">
                      <button @click="quickViewMedia = '{{ asset('storage') }}/' + img; quickViewIsVideo = false" 
                          :class="quickViewMedia === '{{ asset('storage') }}/' + img ? 'border-primary ring-2 ring-primary/30' : 'border-gray-200 opacity-70 hover:opacity-100 hover:border-gray-400'"
                          class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden border transition-all active:scale-95 bg-white">
                        <img :src="'{{ asset('storage') }}/' + img" class="w-full h-full object-cover">
                      </button>
                    </template>
                  </template>

                  <template x-if="selectedProduct.video_path">
                    <button @click="quickViewMedia = '{{ asset('storage') }}/' + selectedProduct.video_path; quickViewIsVideo = true" 
                        :class="quickViewIsVideo ? 'border-primary ring-2 ring-primary/30' : 'border-gray-200 opacity-70 hover:opacity-100 hover:border-gray-400'"
                        class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden border transition-all active:scale-95 bg-gray-100 flex items-center justify-center text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                  </template>
                </div>
              </div>
              
              <!-- Details Half -->
              <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col bg-white relative z-10">
                <span class="text-xs font-display font-bold uppercase tracking-wider text-primary mb-2" x-text="selectedProduct.category?.name || 'Uncategorized'"></span>
                <h2 class="text-2xl sm:text-3xl font-display font-bold text-gray-900 mb-4 leading-tight" x-text="selectedProduct.name"></h2>
                
                <!-- Price -->
                <div class="bg-gray-50 rounded-xl p-5 mb-6">
                  <p class="text-xs text-gray-500 font-medium mb-1">Price</p>
                  <div class="flex items-baseline gap-1">
                    <span class="text-sm text-gray-500">NPR</span>
                    <span class="text-3xl md:text-4xl font-display font-bold text-gray-900" x-text="selectedProduct.price.toLocaleString()"></span>
                  </div>
                </div>
                
                <div class="mb-6">
                  <h4 class="font-display font-bold text-gray-900 mb-2 text-sm">Description</h4>
                  <p class="text-gray-500 text-sm leading-relaxed" x-text="selectedProduct.description"></p>
                </div>
                
                <div class="flex items-center gap-3 text-xs font-medium mb-6">
                  <div :class="selectedProduct.in_stock ? 'bg-green-50 text-green-600 border-green-200' : 'bg-red-50 text-red-500 border-red-200'" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border">
                    <span class="w-2 h-2 rounded-full" :class="selectedProduct.in_stock ? 'bg-green-500' : 'bg-red-500'"></span>
                    <span x-text="selectedProduct.in_stock ? 'In Stock' : 'Out of Stock'"></span>
                  </div>
                  <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-full text-gray-500">
                    <span x-text="selectedProduct.weight_grams + 'g'"></span>
                  </div>
                </div>
                
                <button @click="triggerAddToCart(selectedProduct); closeQuickView();" class="mt-auto w-full bg-primary text-white font-display font-bold py-4 px-8 rounded-full hover:bg-primary-dark active:scale-95 transition-all shadow-lg flex items-center justify-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                  Buy
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>

  <!-- Bundle Selection Modal -->
  <div x-show="bundleSelectionOpen" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 100;">
    <div x-show="bundleSelectionOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="bundleSelectionOpen = false"></div>
    <div x-show="bundleSelectionOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-2xl p-6 sm:p-8 w-full shadow-2xl z-10 mx-auto" style="max-width: 450px;">
      <button @click="bundleSelectionOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
      <h3 class="text-xl font-display font-bold text-gray-900 mb-2">Select Package</h3>
      <p class="text-gray-500 mb-6 text-sm">Choose the best value for you.</p>
      
      <div class="space-y-3">
        <!-- Single Piece (hidden for bundle_only products) -->
        <template x-if="!bundleProduct?.bundle_only">
        <button @click="bundleSelectionOpen = false; if(needsVariants(bundleProduct)) { openVariantModal(bundleProduct, 1, bundleProduct.price, false); } else { processAddToCart(bundleProduct, 1, bundleProduct.price, false, '', ''); }" class="w-full border border-gray-200 bg-white rounded-xl p-4 flex justify-between items-center hover:border-primary/50 hover:bg-red-50 transition text-left group">
          <div>
            <span class="block font-display font-bold text-gray-900 text-base">Single Unit</span>
            <span class="block text-gray-500 text-xs">Standard price</span>
          </div>
          <span class="font-display font-bold text-lg text-gray-900 group-hover:text-primary transition">NPR <span x-text="bundleProduct?.price.toLocaleString()"></span></span>
        </button>
        </template>
        
        <!-- Bundles -->
        <template x-for="bundle in bundleProduct?.bundles" :key="bundle.qty">
          <button @click="bundleSelectionOpen = false; if(needsVariants(bundleProduct)) { openVariantModal(bundleProduct, parseInt(bundle.qty), bundle.price / parseInt(bundle.qty), true); } else { processAddToCart(bundleProduct, parseInt(bundle.qty), bundle.price / parseInt(bundle.qty), true, '', ''); }" class="w-full border-2 border-primary bg-red-50 rounded-xl p-4 flex justify-between items-center hover:bg-red-100 transition text-left group relative overflow-hidden">
            <div class="relative z-10">
              <span class="block font-display font-bold text-gray-900 text-base"><span x-text="bundle.qty"></span> Unit Bundle</span>
              <span class="block text-primary text-xs font-bold">Best Value!</span>
            </div>
            <span class="font-display font-bold text-lg text-gray-900 relative z-10">NPR <span x-text="parseFloat(bundle.price).toLocaleString()"></span></span>
          </button>
        </template>
      </div>
    </div>
  </div>

  <!-- Variant Selection Modal (Color / Size) -->
  <div x-show="variantModalOpen" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 100;">
    <div x-show="variantModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="variantModalOpen = false"></div>
    <div x-show="variantModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-2xl p-6 sm:p-8 w-full shadow-2xl z-10 mx-auto" style="max-width: 450px;">
      <button @click="variantModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
      
      <h3 class="text-xl font-display font-bold text-gray-900 mb-1">Select Options</h3>
      <p class="text-gray-500 mb-6 text-sm" x-text="variantProduct?.name"></p>

      <!-- Color Selection -->
      <template x-if="variantProduct?.color_options?.length > 0">
        <div class="mb-6">
          <label class="block text-sm font-display font-bold text-gray-700 mb-3">Color</label>
          <div class="flex flex-wrap gap-2">
            <template x-for="color in variantProduct.color_options" :key="color">
              <button type="button" @click="selectedColor = color" 
                :class="selectedColor === color ? 'border-primary bg-red-50 text-primary' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400'" 
                class="px-4 py-2 rounded-full border font-medium text-sm transition-all duration-200 active:scale-95"
                x-text="color">
              </button>
            </template>
          </div>
          <p x-show="variantError && !selectedColor" class="text-red-500 text-xs mt-2">Please select a color</p>
        </div>
      </template>

      <!-- Size Selection -->
      <template x-if="variantProduct?.size_options?.length > 0">
        <div class="mb-6">
          <label class="block text-sm font-display font-bold text-gray-700 mb-3">Size</label>
          <div class="flex flex-wrap gap-2">
            <template x-for="size in variantProduct.size_options" :key="size">
              <button type="button" @click="selectedSize = size" 
                :class="selectedSize === size ? 'border-primary bg-red-50 text-primary' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-400'" 
                class="w-12 h-12 rounded-xl border font-bold text-sm transition-all duration-200 active:scale-95 flex items-center justify-center"
                x-text="size">
              </button>
            </template>
          </div>
          <p x-show="variantError && !selectedSize" class="text-red-500 text-xs mt-2">Please select a size</p>
        </div>
      </template>

      <!-- Buy Button -->
      <button @click="confirmVariantAddToCart()" class="mt-4 w-full bg-primary text-white font-display font-bold py-4 rounded-full hover:bg-primary-dark active:scale-95 transition-all shadow-lg flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        Buy
      </button>
    </div>
  </div>



  <!-- The Cart Modal Overlay -->
  <div x-show="cartOpen" x-cloak class="fixed inset-0 z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div x-show="cartOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="cartOpen = false"></div>

    <!-- The Drawer -->
    <div x-show="cartOpen" 
       x-transition:enter="transform transition ease-out duration-300" 
       x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full" 
       x-transition:enter-end="translate-y-0 md:translate-x-0" 
       x-transition:leave="transform transition ease-in duration-300" 
       x-transition:leave-start="translate-y-0 md:translate-x-0" 
       x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full" 
       class="fixed bottom-0 md:top-0 right-0 w-full md:w-[450px] h-[95vh] md:h-screen rounded-t-2xl md:rounded-none flex flex-col bg-white shadow-2xl">
      
      <!-- Grab handle (mobile) -->
      <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto my-3 md:hidden"></div>

      <!-- Header -->
      <div class="px-5 py-4 md:px-6 md:py-6 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-xl md:text-2xl font-display font-bold text-gray-900 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
          Your Cart
        </h2>
        <button @click="cartOpen = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors active:scale-95">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>

      <!-- Cart Content -->
      <div class="flex-1 overflow-y-auto p-6">
        <template x-if="cart.length === 0">
          <div class="text-center py-12 flex flex-col items-center justify-center h-full">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </div>
            <p class="text-lg font-display font-bold text-gray-900 mb-1">Your cart is empty</p>
            <p class="text-gray-500 text-sm mb-6">Add some products to get started!</p>
            <button @click="cartOpen = false" class="bg-primary text-white font-bold py-2.5 px-6 rounded-full hover:bg-primary-dark transition-colors">Browse Products</button>
          </div>
        </template>

        <template x-for="(item, index) in cart" :key="index">
          <div class="flex gap-3 mb-3 bg-gray-50 p-3 rounded-xl items-center">
            <img :src="'{{ asset('storage') }}/' + item.image_path" :alt="item.name" class="w-16 h-16 md:w-20 md:h-20 object-cover bg-white rounded-lg flex-shrink-0">
            <div class="flex-1 min-w-0">
              <h3 class="font-display font-bold text-gray-900 text-sm leading-tight mb-1 line-clamp-2" x-text="item.name"></h3>
              <!-- Variant badges -->
              <div x-show="item.selectedColor || item.selectedSize" class="flex gap-1.5 mb-1.5 flex-wrap">
                <span x-show="item.selectedColor" class="bg-red-50 text-primary text-[10px] font-bold px-2 py-0.5 rounded-full border border-red-200" x-text="item.selectedColor"></span>
                <span x-show="item.selectedSize" class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-full border border-gray-200" x-text="'Size: ' + item.selectedSize"></span>
              </div>
              <p class="text-gray-500 font-medium text-sm mb-2">
                <span x-show="item.isBundle" class="text-primary text-xs mr-1">Bundle: </span>
                NPR <span class="text-gray-900 font-bold" x-text="(item.price * item.quantity).toLocaleString()"></span>
              </p>
              
              <template x-if="!item.isBundle">
                <div class="flex items-center justify-between mt-2">
                  <div class="flex items-center gap-2 bg-white w-max rounded-full p-1 border border-gray-200 shadow-sm">
                    <button @click="updateQuantity(index, -1)" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-primary rounded-full hover:bg-red-50 transition"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg></button>
                    <span class="font-bold w-5 text-center text-sm text-gray-900" x-text="item.quantity"></span>
                    <button @click="updateQuantity(index, 1)" class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-primary rounded-full hover:bg-red-50 transition"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></button>
                  </div>
                  <button @click="cart.splice(index, 1); if(cart.length===0) cartOpen=false; showToast('Item removed from cart');" class="text-gray-400 hover:text-red-500 p-2 rounded-full hover:bg-red-50 transition-colors" title="Remove item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </template>
              <template x-if="item.isBundle">
                <div class="flex items-center justify-between gap-3 bg-red-50 w-max rounded-full px-3 py-1 border border-red-200 mt-1">
                  <span class="text-primary font-bold text-xs" x-text="item.quantity + ' Unit Pack'"></span>
                  <button @click="cart.splice(index, 1); if(cart.length===0) cartOpen=false;" class="text-red-400 hover:text-red-600 ml-2 p-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </template>
            </div>
          </div>
        </template>

        <!-- Quick Add Product -->
        <div x-show="cart.length > 0" class="mt-4">
          <a href="#shop" @click="cartOpen = false" class="w-full bg-transparent border-2 border-dashed border-gray-200 text-gray-400 font-bold py-3 rounded-xl flex items-center justify-center gap-2 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition-colors active:scale-95 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add More Items
          </a>
        </div>

        <!-- Order Form -->
        <div x-show="cart.length > 0" class="mt-6 bg-gray-50 p-5 md:p-6 rounded-xl">
          <h3 class="font-display font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            Delivery Details
          </h3>
          <div class="space-y-4">
            <div class="relative">
              <label class="block text-xs font-bold text-gray-500 mb-1">Full Name</label>
              <input type="text" x-model="customer.name" :class="formSubmitted && !customer.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : 'border-gray-200 focus:border-primary focus:ring-primary/20'" class="w-full bg-white border rounded-xl px-4 py-3 text-gray-900 focus:ring-2 transition-colors text-sm placeholder-gray-400" placeholder="Enter your name">
            </div>
            <div class="relative">
              <label class="block text-xs font-bold text-gray-500 mb-1">Phone Number</label>
              <input type="tel" x-model="customer.phone" :class="formSubmitted && !customer.phone ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : 'border-gray-200 focus:border-primary focus:ring-primary/20'" class="w-full bg-white border rounded-xl px-4 py-3 text-gray-900 focus:ring-2 transition-colors text-sm placeholder-gray-400" placeholder="Enter phone number">
            </div>
            <div class="relative">
              <label class="block text-xs font-bold text-gray-500 mb-1">Delivery Address</label>
              <textarea x-model="customer.address" rows="2" :class="formSubmitted && !customer.address ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : 'border-gray-200 focus:border-primary focus:ring-primary/20'" class="w-full bg-white border rounded-xl px-4 py-3 text-gray-900 focus:ring-2 transition-colors text-sm placeholder-gray-400 resize-none" placeholder="Enter delivery address"></textarea>
            </div>
            
            <div class="pt-1">
              <label class="block text-xs font-bold text-gray-500 mb-1">Delivery Area</label>
              <select x-model="customer.delivery_location" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:border-primary focus:ring-2 focus:ring-primary/20 cursor-pointer text-sm transition-colors">
                <option value="inside">Inside Valley (+ NPR {{ setting('delivery_charge_inside', 50) }})</option>
                <option value="outside">Outside Valley (+ NPR {{ setting('delivery_charge_outside', 100) }})</option>
              </select>
            </div>

            <p x-show="formError" x-text="formError" class="text-red-500 text-xs bg-red-50 p-3 rounded-xl border border-red-200"></p>
          </div>
        </div>
      </div>

      <!-- Footer (Total & Button) -->
      <div x-show="cart.length > 0" class="p-6 bg-white border-t border-gray-100 mt-auto">
        <div class="flex justify-between items-end mb-2">
          <span class="text-gray-500 text-sm">Subtotal</span>
          <span class="text-sm font-bold text-gray-900">NPR <span x-text="itemsTotal.toLocaleString()"></span></span>
        </div>
        <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-4">
          <span class="text-gray-500 text-sm">Delivery</span>
          <span class="text-sm font-bold text-gray-900">NPR <span x-text="deliveryCharge.toLocaleString()"></span></span>
        </div>
        <div class="flex justify-between items-end mb-6">
          <span class="text-primary text-base font-display font-bold">Total</span>
          <span class="text-2xl font-display font-bold text-gray-900">NPR <span x-text="cartTotal.toLocaleString()"></span></span>
        </div>
        
        <button @click="placeOrder()" :disabled="isSubmitting" class="w-full bg-primary text-white font-display font-bold py-4 rounded-full hover:bg-primary-dark active:scale-95 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
          <span x-show="!isSubmitting">Place Order</span>
          <span x-show="isSubmitting">Processing...</span>
          <svg x-show="!isSubmitting" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Celebration Overlay -->
  <div x-show="showCelebration" x-transition.opacity.duration.500ms class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="text-center p-10 bg-white rounded-2xl shadow-2xl max-w-sm mx-4">
      <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
      </div>
      <h2 class="text-2xl font-display font-bold text-gray-900 mb-2">Order Placed!</h2>
      <p class="text-gray-500 text-sm">We'll contact you within 5 minutes to confirm your order.</p>
    </div>
  </div>

  <script>
    function shopData() {
      return {
        products: @json($products),
        cart: [],
        cartOpen: false,
        mobileMenuOpen: false,
        mobileSearchOpen: false,
        scrolled: false,
        searchQuery: '',
        activeCategory: 'all',
        
        // Quick View Data
        quickViewOpen: false,
        selectedProduct: null,
        quickViewMedia: '',
        quickViewIsVideo: false,

        customer: {
          name: '', phone: '', address: '', delivery_location: 'inside'
        },
        formError: '',
        formSubmitted: false,
        isSubmitting: false,
        showCelebration: false,
        
        // Toast state
        toastVisible: false,
        toastMessage: '',

        bundleSelectionOpen: false,
        bundleProduct: null,

        // Variant selection state
        variantModalOpen: false,
        variantProduct: null,
        selectedColor: '',
        selectedSize: '',
        variantError: false,
        pendingVariantQty: 1,
        pendingVariantPrice: 0,
        pendingVariantIsBundle: false,

        init() {
          // Load cart from localStorage
          const savedCart = localStorage.getItem('cart');
          if (savedCart) {
            this.cart = JSON.parse(savedCart);
          }
          
          this.$watch('cart', val => localStorage.setItem('cart', JSON.stringify(val)));
          
          this.scrolled = window.scrollY > 20;
          
          window.addEventListener('scroll', () => {
            this.scrolled = window.scrollY > 20;
          });
          this.trackEvent('page_view', { url: window.location.href });
        },

        trackEvent(eventType, data = {}) {
          fetch('{{ url("track-event") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              event_type: eventType,
              ...data
            })
          }).catch(e => console.error('Tracking error', e));
        },

        get filteredProducts() {
          return this.products.filter(p => {
            const matchesSearch = p.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                       (p.description || '').toLowerCase().includes(this.searchQuery.toLowerCase());
            
            const matchesCategory = this.activeCategory === 'all' || 
                        (p.category && p.category.slug === this.activeCategory);

            return matchesSearch && matchesCategory;
          });
        },

        openQuickView(product) {
          this.selectedProduct = product;
          this.quickViewMedia = '{{ asset('storage') }}/' + product.image_path;
          this.quickViewIsVideo = false;
          this.quickViewOpen = true;
          this.trackEvent('view_product', { product_id: product.id, category_id: product.category_id });
        },

        closeQuickView() {
          this.quickViewOpen = false;
          setTimeout(() => { this.selectedProduct = null; }, 300);
        },

        // Check if product needs variant selection (product-level)
        needsVariants(product) {
          return (product.color_options && product.color_options.length > 0) ||
              (product.size_options && product.size_options.length > 0);
        },

        triggerAddToCart(product) {
          // Bundle-only card: add directly with bundle qty and unit price
          if (product.is_bundle_card && product.bundle_qty) {
            const unitPrice = product.bundle_price / product.bundle_qty;
            if (this.needsVariants(product)) {
              this.openVariantModal(product, product.bundle_qty, unitPrice, true);
            } else {
              this.processAddToCart(product, product.bundle_qty, unitPrice, true, '', '');
            }
          } else if (product.bundles && product.bundles.length > 0) {
            this.bundleProduct = product;
            this.bundleSelectionOpen = true;
          } else if (this.needsVariants(product)) {
            this.openVariantModal(product, 1, product.price, false);
          } else {
            this.processAddToCart(product, 1, product.price, false, '', '');
          }
        },

        openVariantModal(product, qty, unitPrice, isBundle) {
          this.variantProduct = product;
          this.selectedColor = '';
          this.selectedSize = '';
          this.variantError = false;
          this.pendingVariantQty = qty;
          this.pendingVariantPrice = unitPrice;
          this.pendingVariantIsBundle = isBundle;
          this.variantModalOpen = true;
        },

        confirmVariantAddToCart() {
          const p = this.variantProduct;
          const needsColor = p.color_options && p.color_options.length > 0;
          const needsSize = p.size_options && p.size_options.length > 0;

          if ((needsColor && !this.selectedColor) || (needsSize && !this.selectedSize)) {
            this.variantError = true;
            return;
          }

          this.variantModalOpen = false;
          this.processAddToCart(
            this.variantProduct, 
            this.pendingVariantQty, 
            this.pendingVariantPrice, 
            this.pendingVariantIsBundle,
            this.selectedColor,
            this.selectedSize
          );
        },

        processAddToCart(product, qty, unitPrice, isBundle, color = '', size = '') {
          const cartItemId = `${product.id}_${qty}_${color}_${size}`;
          const existing = this.cart.find(i => i.cartItemId === cartItemId);
          
          if (existing) {
            existing.quantity += qty;
          } else {
            this.cart.push({ 
              ...product, 
              quantity: qty, 
              price: unitPrice, 
              isBundle: isBundle, 
              cartItemId: cartItemId,
              selectedColor: color || null,
              selectedSize: size || null
            });
          }
          this.bundleSelectionOpen = false;
          this.cartOpen = true;
          
          this.showToast(product.name + " added to cart!");

          // Facebook Pixel: AddToCart
          if (typeof fbq !== 'undefined') {
            fbq('track', 'AddToCart', {
              content_name: product.name,
              content_ids: [String(product.id)],
              content_type: 'product',
              value: unitPrice * qty,
              currency: 'NPR'
            });
          }
          this.trackEvent('add_to_cart', { product_id: product.id, category_id: product.category_id });
        },

        updateQuantity(index, delta) {
          const item = this.cart[index];
          item.quantity += delta;
          if (item.quantity <= 0) {
            this.cart.splice(index, 1);
            if(this.cart.length === 0) this.cartOpen = false;
          }
        },

        toggleCart() {
          if (this.cart.length > 0) {
            this.cartOpen = !this.cartOpen;
            // Facebook Pixel: InitiateCheckout
            if (this.cartOpen && typeof fbq !== 'undefined') {
              fbq('track', 'InitiateCheckout', {
                value: this.itemsTotal,
                currency: 'NPR',
                num_items: this.totalCartQuantity
              });
            }
            if (this.cartOpen) {
              this.trackEvent('initiate_checkout', { url: window.location.href });
            }
          } else {
            this.showToast("Your cart is empty. Add some products first!");
          }
        },

        get totalCartQuantity() {
          return this.cart.reduce((total, item) => total + item.quantity, 0);
        },

        get itemsTotal() {
          return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        },

        get deliveryCharge() {
          return this.customer.delivery_location === 'inside' ? {{ (int) setting('delivery_charge_inside', 50) }} : {{ (int) setting('delivery_charge_outside', 100) }};
        },

        get cartTotal() {
          return this.itemsTotal + this.deliveryCharge;
        },

        async placeOrder() {
          this.formSubmitted = true;
          if (!this.customer.name || !this.customer.phone || !this.customer.address) {
            this.formError = 'Please fill out all delivery details.';
            return;
          }
          
          this.formError = '';
          this.isSubmitting = true;

          const payload = {
            customer_name: this.customer.name,
            customer_phone: this.customer.phone,
            address: this.customer.address,
            delivery_location: this.customer.delivery_location,
            source: 'Web',
            items: this.cart.map(item => ({
              id: item.id,
              quantity: item.quantity,
              color: item.selectedColor || null,
              size: item.selectedSize || null
            }))
          };

          try {
            const response = await fetch('{{ url('checkout') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
              },
              body: JSON.stringify(payload)
            });

            if (response.ok) {
              // Facebook Pixel: Purchase
              if (typeof fbq !== 'undefined') {
                fbq('track', 'Purchase', {
                  value: this.cartTotal,
                  currency: 'NPR',
                  content_type: 'product',
                  contents: this.cart.map(item => ({
                    id: String(item.id),
                    quantity: item.quantity
                  }))
                });
              }
              this.trackEvent('purchase', { url: window.location.href });

              // Close cart, show success state
              this.cartOpen = false;
              this.showCelebration = true;
              
              // Reset form
              this.formSubmitted = false;
              this.cart = [];
              this.customer = { name: '', phone: '', address: '', delivery_location: 'inside' };
              this.isSubmitting = false;
              
              // Hide success after a few seconds
              setTimeout(() => {
                this.showCelebration = false;
              }, 5000);

            } else {
              const errorData = await response.json();
              this.formError = errorData.message || 'Error placing order.';
              this.isSubmitting = false;
            }
          } catch (error) {
            this.formError = 'Network error. Please try again.';
            this.isSubmitting = false;
          }
        },
        
        showToast(message) {
          this.toastMessage = message;
          this.toastVisible = true;
          if (this.toastTimeout) clearTimeout(this.toastTimeout);
          this.toastTimeout = setTimeout(() => {
            this.toastVisible = false;
          }, 3000);
        }
      }
    }
  </script>

  <!-- Floating Bottom Navigation Bar (Mobile only) -->
  <nav class="fixed bottom-4 left-4 right-4 z-40 md:hidden bg-white/90 backdrop-blur-lg rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 px-6 py-3" x-data="{ activeNav: 'home' }">
    <div class="flex items-center justify-between">
      <!-- Home -->
      <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 transition-colors" :class="activeNav === 'home' ? 'text-gray-900' : 'text-gray-400 hover:text-gray-900'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
      </a>
      <!-- Shop -->
      <a href="{{ route('shop') }}" class="flex flex-col items-center gap-1 transition-colors" :class="activeNav === 'shop' ? 'text-gray-900' : 'text-gray-400 hover:text-gray-900'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
      </a>
      <!-- Categories -->
      <button @click="window.location.href='{{ route('shop') }}'" class="w-12 h-12 bg-gray-900 rounded-full flex items-center justify-center text-white shadow-lg shadow-gray-900/30 transform -translate-y-4 border-4 border-white active:scale-95 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
      </button>
      <!-- Cart -->
      <button @click="activeNav = 'cart'; toggleCart()" class="flex flex-col items-center gap-1 transition-colors relative" :class="activeNav === 'cart' ? 'text-gray-900' : 'text-gray-400 hover:text-gray-900'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] font-black min-w-[16px] h-[16px] rounded-full flex items-center justify-center border-2 border-white"></span>
      </button>
      <!-- Company Profile -->
      <a href="{{ route('company.profile') }}" class="flex flex-col items-center gap-1 transition-colors" :class="activeNav === 'profile' ? 'text-gray-900' : 'text-gray-400 hover:text-gray-900'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
      </a>
    </div>
  </nav>
</body>
</html>
