<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $product->name }} | {{ config('app.name', 'Chhito Pasal') }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">

  @if(setting('store_favicon'))
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('store_favicon')) }}">
  @else
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">
  @endif

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  {{-- Alpine.js is already bundled via app.js — do NOT load the CDN version too --}}

  <style>
    [x-cloak] { display: none !important; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.6s ease forwards; }
    .thumb-active { outline: 2px solid #FFB627; outline-offset: 2px; opacity: 1 !important; }
    .thumb-inactive { opacity: 0.5; }
    .thumb-inactive:hover { opacity: 0.85; }
    @media (min-width: 768px) {
      .pdp-layout { flex-direction: row !important; }
      .pdp-gallery { width: 55% !important; }
      .pdp-details { width: 45% !important; }
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
    fbq('track', 'ViewContent', {
      content_name: '{{ addslashes($product->name) }}',
      content_ids: ['{{ $product->id }}'],
      content_type: 'product',
      value: {{ $product->price }},
      currency: 'NPR'
    });
  </script>
  <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ setting('facebook_pixel_id') }}&ev=PageView&noscript=1"/></noscript>
  @endif
</head>
<body class="antialiased text-gray-900 overflow-x-hidden selection:bg-primary selection:text-white"
   x-data="shopData()">
  {{-- Google Tag Manager (noscript) --}}
  @if(setting('google_tag_manager_id'))
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ setting('google_tag_manager_id') }}"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  @endif

  <!-- Header -->
  <header class="cp-header fixed top-0 left-0 right-0 z-40 hidden md:block">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-full">
      
      <a href="{{ route('home') }}" class="flex items-center gap-2 group flex-shrink-0 min-w-0">
        @if(setting('store_logo'))
          <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-8 md:h-9 w-auto object-contain max-w-[120px] md:max-w-[140px] mix-blend-multiply">
        @endif
        @php
          $brandName = setting('store_name', 'Chhito Pasal');
          $brandParts = explode(' ', $brandName, 2);
        @endphp
        <span class="text-[15px] md:text-lg font-black tracking-tight leading-none">
          <span class="text-primary">{{ $brandParts[0] }}</span><span class="text-gray-900">{{ isset($brandParts[1]) ? ' '.$brandParts[1] : '' }}</span>
        </span>
      </a>

      <div class="hidden md:flex items-center flex-1 max-w-2xl px-12 gap-8">
        <nav class="flex gap-6 font-bold text-gray-600">
          <a href="{{ route('home') }}" class="hover:text-gray-900 transition-colors">Home</a>
          <a href="{{ url('/#shop') }}" class="hover:text-gray-900 transition-colors">Shop</a>
        </nav>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('home') }}" class="md:hidden p-2 rounded-xl text-gray-600 active:scale-95 transition-transform hover:bg-gray-100">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <button @click="toggleCart()" class="relative bg-gray-900 text-white p-3 rounded-xl hover:bg-gray-800 transition active:scale-95 shadow-sm hidden md:flex items-center gap-2 group">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
          <span class="font-bold">Cart</span>
          <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" x-transition class="absolute -top-2 -right-2 bg-primary text-white text-xs font-black w-6 h-6 rounded-full flex items-center justify-center border-2 border-white shadow-sm"></span>
        </button>
        <button @click="toggleCart()" class="relative p-2 rounded-xl text-gray-600 active:scale-95 transition-transform hover:bg-gray-100 md:hidden">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" x-transition class="absolute -top-0.5 -right-0.5 bg-primary text-white text-[9px] font-black min-w-[18px] h-[18px] rounded-full flex items-center justify-center border-[1.5px] border-white"></span>
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile Header -->
  <header class="md:hidden absolute top-0 left-0 right-0 z-50 px-4 py-4 pt-safe flex items-center justify-between pointer-events-none">
    <button class="w-10 h-10 flex items-center justify-center text-gray-900 bg-white/80 backdrop-blur-md rounded-full shadow-sm pointer-events-auto active:scale-95 transition-transform" onclick="history.back()">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
    </button>
    <div class="flex items-center gap-2 pointer-events-auto">
      <button @click="toggleCart()" class="w-10 h-10 relative flex items-center justify-center text-gray-900 bg-white/80 backdrop-blur-md rounded-full shadow-sm active:scale-95 transition-transform">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
        <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" class="absolute -top-1 -right-1 w-4 h-4 bg-red-600 text-white rounded-full border border-white text-[10px] font-black flex items-center justify-center"></span>
      </button>
    </div>
  </header>

  <!-- Breadcrumb -->
  <nav class="hidden md:block pt-[60px] md:pt-[90px] max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 mb-4 md:mb-6">
    <div class="flex items-center gap-1.5 text-[12px] md:text-[13px] font-semibold text-gray-400">
      <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 transition-colors">Home</a>
      <span>›</span>
      <a href="{{ url('/#shop') }}" class="text-gray-600 hover:text-gray-900 transition-colors">Shop</a>
      <span>›</span>
      <span class="text-gray-900 truncate max-w-[200px]">{{ $product->name }}</span>
    </div>
  </nav>

  <main class="max-w-[1300px] mx-auto px-0 md:px-8 pb-32 md:pb-20 min-h-[60vh]">
    <div class="pdp-layout fade-up flex flex-col md:rounded-[24px] overflow-hidden bg-white shadow-none md:shadow-sm"
       x-data="{ activeMedia: '{{ asset('storage/' . $product->image_path) }}', isVideo: false }">

      <!-- Gallery Side -->
      <div class="pdp-gallery w-full bg-gray-50 md:rounded-2xl overflow-hidden relative">
        <!-- Main Image -->
        <div class="aspect-[4/5] md:aspect-square w-full relative">
          <template x-if="!isVideo">
            <img :src="activeMedia" alt="{{ $product->name }}" class="w-full h-full object-cover transition-opacity duration-300">
          </template>
          <template x-if="isVideo">
            <video :src="activeMedia" controls autoplay muted class="w-full h-full object-contain bg-black"></video>
          </template>
        </div>

        <!-- Thumbnails -->
        <div class="absolute bottom-6 left-0 right-0 z-10 flex justify-center md:justify-start gap-4 md:gap-2.5 overflow-x-auto no-scrollbar px-4">
          <button @click="activeMedia = '{{ asset('storage/' . $product->image_path) }}'; isVideo = false"
              :class="activeMedia === '{{ asset('storage/' . $product->image_path) }}' ? 'thumb-active' : 'thumb-inactive'"
              class="w-14 h-14 md:w-16 md:h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 border-border cursor-pointer transition-all active:scale-95 bg-white">
            <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover">
          </button>
          @if($product->additional_images)
            @foreach($product->additional_images as $img)
            <button @click="activeMedia = '{{ asset('storage/' . $img) }}'; isVideo = false"
                :class="activeMedia === '{{ asset('storage/' . $img) }}' ? 'thumb-active' : 'thumb-inactive'"
                class="w-14 h-14 md:w-16 md:h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 border-border cursor-pointer transition-all active:scale-95 bg-white">
              <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
            </button>
            @endforeach
          @endif
          @if($product->video_path)
          <button @click="activeMedia = '{{ asset('storage/' . $product->video_path) }}'; isVideo = true"
              :class="activeMedia === '{{ asset('storage/' . $product->video_path) }}' ? 'thumb-active' : 'thumb-inactive'"
              class="w-14 h-14 md:w-16 md:h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 border-gray-200 cursor-pointer bg-gray-900 flex items-center justify-center transition-all active:scale-95">
            <svg class="w-6 h-6 md:w-7 md:h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </button>
          @endif
        </div>
      </div>

      <!-- Details Side -->
      <div class="pdp-details w-full px-5 py-6 md:px-8 md:py-8 flex flex-col bg-white md:rounded-r-2xl relative -mt-6 md:mt-0 rounded-t-3xl z-10 shadow-[0_-10px_20px_rgba(0,0,0,0.05)] md:shadow-none border-t border-gray-100 md:border-none">
        <!-- Mobile Title Row -->
        <div class="flex items-start justify-between mb-4">
          <div>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 leading-tight mb-1">{{ $product->name }}</h1>
            @if($product->bundle_only)
              <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs font-black uppercase tracking-wider px-2.5 py-1 rounded-lg border border-amber-200 mb-1">
                📦 Bundle Only — Available in packs
              </span>
            @endif
            <span class="text-xs font-medium text-gray-400">{{ $product->category->name ?? 'Uncategorized' }}</span>
          </div>
          <div class="flex items-center gap-1 bg-gray-50 px-2.5 py-1.5 rounded-lg border border-gray-100 mt-1">
            <svg class="w-4 h-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
            <span class="text-sm font-black text-gray-900">4.8</span>
          </div>
        </div>

        <!-- Price block (Desktop only since mobile has sticky bar) -->
        <div class="hidden md:block bg-gradient-to-br from-amber-50 to-amber-100/60 border border-primary/25 rounded-2xl p-4 md:p-5 mb-4 relative overflow-hidden">
          <div class="absolute -right-5 -top-5 w-20 h-20 bg-primary/20 rounded-full blur-2xl"></div>
          <p class="text-[10px] md:text-[11px] font-bold uppercase tracking-widest text-amber-700 mb-1 relative z-10">Our Price</p>
          <div class="flex items-baseline gap-1.5 relative z-10">
            @if($product->is_flash_sale)
              <span class="text-lg font-bold text-gray-400 line-through">Rs.{{ number_format($product->original_price) }}</span>
              <span class="text-base md:text-lg font-bold text-[#FF4C4C] ml-2">Rs.</span>
              <span class="text-3xl md:text-5xl font-black text-[#FF4C4C] tracking-tighter leading-none">{{ number_format($product->price) }}</span>
            @else
              <span class="text-base md:text-lg font-bold text-amber-600">Rs.</span>
              <span class="text-3xl md:text-5xl font-black text-gray-900 tracking-tighter leading-none">{{ number_format($product->price) }}</span>
            @endif
          </div>
        </div>

        <!-- Stock + Weight badges -->
        <div class="flex flex-wrap gap-2 mb-6 md:mb-4">
          @if($product->in_stock)
          <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-success-soft text-success border border-success/15">
            <span class="w-1.5 h-1.5 rounded-full bg-success shadow-[0_0_6px_rgba(0,196,140,0.5)]"></span>
            In Stock
          </div>
          @else
          <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-red-50 text-primary border border-primary/15">
            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
            Out of Stock
          </div>
          @endif
          <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l3 9a5.002 5.002 0 01-6.001 0M18 7l-3 9m-6-9l6-2m0 0V3" /></svg>
            {{ $product->weight_grams }}g
          </div>
        </div>

        <!-- Available Packages (For bundle-only products) -->
        @if($product->bundle_only && !empty($product->bundles) && count($product->bundles) > 1)
        <div class="mb-6">
          <h4 class="text-xs md:text-sm font-black text-gray-900 mb-3 uppercase tracking-wider">Available Packages</h4>
          <div class="grid grid-cols-2 gap-3">
            @foreach($product->bundles as $bundle)
            <a href="{{ url('product/' . $product->slug) }}?bundle={{ $bundle['qty'] }}" 
              class="border-2 rounded-xl p-3 flex flex-col items-center justify-center transition-all {{ ($selectedBundle && $selectedBundle['qty'] == $bundle['qty']) ? 'border-mango bg-mango/10' : 'border-gray-200 hover:border-mango/50 hover:bg-gray-50' }}">
              <span class="font-black text-gray-900">{{ $bundle['qty'] }} Pack</span>
              <span class="text-sm font-bold text-gray-500">Rs.{{ number_format($bundle['price']) }}</span>
            </a>
            @endforeach
          </div>
        </div>
        @endif

        <!-- Buy button (hidden on mobile — shown as sticky bar instead) -->
        <button @click="triggerAddToCart({{ json_encode($product) }})" class="cp-btn-buy hidden md:flex w-full py-4 px-6 rounded-2xl text-base items-center justify-center gap-3 mb-5">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
          Buy Now
        </button>

        <!-- Tabs for Mobile -->
        <div class="md:hidden flex bg-gray-50 rounded-[1.25rem] p-1.5 mb-6">
          <button class="flex-1 bg-white shadow-sm text-gray-900 font-bold text-sm py-2.5 rounded-[1rem] transition-all">Description</button>
          <button class="flex-1 text-gray-500 font-bold text-sm py-2.5 rounded-[1rem] transition-all">Features</button>
        </div>

        <!-- Description -->
        <div class="mb-4">
          <h4 class="hidden md:block text-xs md:text-sm font-black text-gray-900 mb-2 uppercase tracking-wider">Description</h4>
          <p class="text-[15px] font-medium text-gray-500 md:text-gray-600 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
        </div>

        <!-- Trust badges / Attributes row -->
        <div class="flex justify-between md:grid md:grid-cols-2 gap-2 md:overflow-x-auto no-scrollbar mt-auto pt-6 border-t md:border-divider border-transparent">
          <div class="flex flex-col items-center gap-2 md:flex-row md:cp-trust-badge group">
            <div class="w-[3.25rem] h-[3.25rem] md:w-auto md:h-auto rounded-full bg-gray-50 md:bg-transparent flex items-center justify-center border border-gray-100 md:border-none group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-gray-700 md:text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
            </div>
            <span class="text-[11px] md:text-xs font-black text-gray-900 md:text-gray-900 text-center uppercase tracking-tight">Secure<br class="md:hidden"><span class="text-gray-400 font-bold hidden md:inline"> Checkout</span></span>
          </div>
          <div class="flex flex-col items-center gap-2 md:flex-row md:cp-trust-badge group">
            <div class="w-[3.25rem] h-[3.25rem] md:w-auto md:h-auto rounded-full bg-gray-50 md:bg-transparent flex items-center justify-center border border-gray-100 md:border-none group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-gray-700 md:text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <span class="text-[11px] md:text-xs font-black text-gray-900 md:text-gray-900 text-center uppercase tracking-tight">Fast<br class="md:hidden"><span class="text-gray-400 font-bold hidden md:inline"> Delivery</span></span>
          </div>
          <div class="flex flex-col items-center gap-2 md:flex-row md:cp-trust-badge group">
            <div class="w-[3.25rem] h-[3.25rem] md:w-auto md:h-auto rounded-full bg-gray-50 md:bg-transparent flex items-center justify-center border border-gray-100 md:border-none group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-gray-700 md:text-success flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
            </div>
            <span class="text-[11px] md:text-xs font-black text-gray-900 md:text-gray-900 text-center uppercase tracking-tight">Cash on<br class="md:hidden"><span class="text-gray-400 font-bold hidden md:inline"> Delivery</span></span>
          </div>
          <div class="flex flex-col items-center gap-2 md:flex-row md:cp-trust-badge group">
            <div class="w-[3.25rem] h-[3.25rem] md:w-auto md:h-auto rounded-full bg-gray-50 md:bg-transparent flex items-center justify-center border border-gray-100 md:border-none group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-gray-700 md:text-pink-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            </div>
            <span class="text-[11px] md:text-xs font-black text-gray-900 md:text-gray-900 text-center uppercase tracking-tight">Top<br class="md:hidden"><span class="text-gray-400 font-bold hidden md:inline"> Quality</span></span>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-900 text-white pt-12 md:pt-20 pb-28 md:pb-10 mt-6 md:mt-10">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Mobile App-like Footer -->
      <div class="md:hidden flex flex-col items-center justify-center text-center">
        @if(setting('store_logo'))
          <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-8 w-auto object-contain mb-4 grayscale opacity-50 filter brightness-0 invert">
        @endif
        <div class="flex gap-4 text-[11px] font-bold text-gray-500 mb-4 uppercase tracking-wider">
          <a href="#" class="hover:text-white transition-colors">Terms</a>
          <span>&bull;</span>
          <a href="#" class="hover:text-white transition-colors">Privacy</a>
          <span>&bull;</span>
          <a href="#" class="hover:text-white transition-colors">Help</a>
        </div>
        <p class="text-[10px] text-gray-600 font-bold uppercase tracking-[0.2em]">
          App Version 1.0.4
        </p>
        <p class="text-[9px] text-gray-600 mt-2 font-medium">
          &copy; {{ date('Y') }} {{ setting('store_name', 'Chhito Pasal') }}
        </p>
      </div>

      <!-- Desktop Copyright -->
      <div class="hidden md:block text-center text-gray-400 font-medium text-sm">
        <p>&copy; {{ date('Y') }} {{ setting('store_name', 'Chhito Pasal') }}. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Mobile Floating Buy Button -->
  <div class="md:hidden fixed bottom-4 left-4 right-4 z-40">
    <button @click="triggerAddToCart({{ json_encode($product) }})" class="w-full bg-[#18181b] text-white rounded-[2rem] p-2 flex items-center justify-between shadow-[0_8px_30px_rgb(0,0,0,0.25)] active:scale-95 transition-transform">
      <span class="pl-5 font-bold text-sm tracking-wide">Buy</span>
      <div class="flex items-center gap-3 bg-white/10 rounded-full px-5 py-3">
        <div class="flex flex-col items-end">
          @if($product->is_flash_sale)
            <span class="text-[10px] font-bold text-gray-400 line-through leading-none">Rs. {{ number_format($product->original_price) }}</span>
            <span class="font-black text-sm tracking-wide text-[#FF4C4C] leading-none">Rs. {{ number_format($product->price) }}</span>
          @else
            <span class="font-bold text-sm tracking-wide">Rs. {{ number_format($product->price) }}</span>
          @endif
        </div>
        <span class="bg-white text-gray-900 text-[10px] font-black px-2 py-0.5 rounded-full flex items-center gap-1 shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-purple-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" /></svg>
          +35
        </span>
      </div>
    </button>
  </div>

  <!-- Bundle Selection Modal -->
  <div x-show="bundleSelectionOpen" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 100;">
    <div x-show="bundleSelectionOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="bundleSelectionOpen = false"></div>
    <div x-show="bundleSelectionOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-3xl p-6 sm:p-8 w-full shadow-2xl z-10 mx-auto" style="max-width: 450px;">
      <button @click="bundleSelectionOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-900 bg-gray-50 p-2 rounded-full"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
      <h3 class="text-2xl font-black text-gray-900 mb-2">Choose Package</h3>
      <p class="text-gray-500 mb-6 font-medium">Select a bundle to save more!</p>
      
      <div class="space-y-3">
        <!-- Single Piece -->
        <!-- Single Piece (hidden for bundle_only products) -->
        @if(!$product->bundle_only)
        <button @click="bundleSelectionOpen = false; if(needsVariants(bundleProduct)) { openVariantModal(bundleProduct, 1, bundleProduct.price, false); } else { processAddToCart(bundleProduct, 1, bundleProduct.price, false, '', ''); }" class="w-full border-2 border-gray-200 rounded-2xl p-4 flex justify-between items-center hover:border-mango hover:bg-mango/5 transition text-left group">
          <div>
            <span class="block font-black text-gray-900 text-lg">Single Piece</span>
            <span class="block text-gray-500 text-sm">Standard price</span>
          </div>
          <span class="font-black text-xl text-gray-900 group-hover:text-mango transition">Rs.<span x-text="bundleProduct?.price.toLocaleString()"></span></span>
        </button>
        @endif
        
        <!-- Bundles -->
        <template x-for="bundle in bundleProduct?.bundles" :key="bundle.qty">
          <button @click="bundleSelectionOpen = false; if(needsVariants(bundleProduct)) { openVariantModal(bundleProduct, parseInt(bundle.qty), bundle.price / parseInt(bundle.qty), true); } else { processAddToCart(bundleProduct, parseInt(bundle.qty), bundle.price / parseInt(bundle.qty), true, '', ''); }" class="w-full border-2 border-mango bg-mango/10 rounded-2xl p-4 flex justify-between items-center hover:bg-mango/20 transition text-left group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-mango/30 rounded-full blur-xl"></div>
            <div class="relative z-10">
              <span class="block font-black text-gray-900 text-lg"><span x-text="bundle.qty"></span> Pieces Bundle</span>
              <span class="block text-mango text-sm font-bold">Best Value!</span>
            </div>
            <span class="font-black text-xl text-gray-900 relative z-10">Rs.<span x-text="parseFloat(bundle.price).toLocaleString()"></span></span>
          </button>
        </template>
      </div>
    </div>
  </div>

  <!-- Variant Selection Modal (Color / Size) -->
  <div x-show="variantModalOpen" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 100;">
    <div x-show="variantModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="variantModalOpen = false"></div>
    <div x-show="variantModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-3xl p-6 sm:p-8 w-full shadow-2xl z-10 mx-auto" style="max-width: 450px;">
      <button @click="variantModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-900 bg-gray-50 p-2 rounded-full"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
      <h3 class="text-2xl font-black text-gray-900 mb-1">Select Options</h3>
      <p class="text-gray-500 mb-6 font-medium text-sm" x-text="variantProduct?.name"></p>
      <!-- Color Selection -->
      <template x-if="variantProduct?.category?.has_color_variants && variantProduct?.category?.color_options?.length > 0">
        <div class="mb-6">
          <label class="block text-sm font-black text-gray-700 mb-3 uppercase tracking-wider">Color</label>
          <div class="flex flex-wrap gap-2">
            <template x-for="color in variantProduct.category.color_options" :key="color">
              <button type="button" @click="selectedColor = color" :class="selectedColor === color ? 'border-gray-900 bg-gray-900 text-white shadow-lg scale-105' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400'" class="px-4 py-2 rounded-xl border-2 font-bold text-sm transition-all duration-200 active:scale-95" x-text="color"></button>
            </template>
          </div>
          <p x-show="variantError && !selectedColor" class="text-red-500 text-xs font-bold mt-2">Please select a color</p>
        </div>
      </template>
      <!-- Size Selection -->
      <template x-if="variantProduct?.category?.has_size_variants && variantProduct?.category?.size_options?.length > 0">
        <div class="mb-6">
          <label class="block text-sm font-black text-gray-700 mb-3 uppercase tracking-wider">Size</label>
          <div class="flex flex-wrap gap-2">
            <template x-for="size in variantProduct.category.size_options" :key="size">
              <button type="button" @click="selectedSize = size" :class="selectedSize === size ? 'border-gray-900 bg-gray-900 text-white shadow-lg scale-105' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400'" class="w-14 h-14 rounded-xl border-2 font-black text-sm transition-all duration-200 active:scale-95 flex items-center justify-center" x-text="size"></button>
            </template>
          </div>
          <p x-show="variantError && !selectedSize" class="text-red-500 text-xs font-bold mt-2">Please select a size</p>
        </div>
      </template>
      <button @click="confirmVariantAddToCart()" class="mt-4 w-full bg-mango text-gray-900 font-black py-4 rounded-2xl hover:bg-yellow-400 active:scale-95 transition-all shadow-sm flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        Buy
      </button>
    </div>
  </div>

  <!-- The Cart Modal Overlay -->
  <div x-show="cartOpen" x-cloak class="fixed inset-0 z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <div x-show="cartOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="cartOpen = false"></div>
    <div x-show="cartOpen" 
       x-transition:enter="transform transition ease-out duration-300 sm:duration-400" 
       x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full" 
       x-transition:enter-end="translate-y-0 md:translate-x-0" 
       x-transition:leave="transform transition ease-in duration-300 sm:duration-400" 
       x-transition:leave-start="translate-y-0 md:translate-x-0" 
       x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full" 
       class="fixed bottom-0 md:top-0 right-0 w-full md:w-[450px] h-[85vh] md:h-screen bg-white shadow-2xl flex flex-col z-50">
      
      <div class="px-6 py-6 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-2xl font-black text-gray-900 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-mango" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
          Your Cart
        </h2>
        <button @click="cartOpen = false" class="text-gray-400 hover:text-gray-900 bg-gray-50 p-2 rounded-full transition-colors active:scale-95">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto p-6 bg-[#F8FAFC]">
        <template x-for="(item, index) in cart" :key="index">
          <div class="flex gap-4 mb-4 bg-white p-4 rounded-3xl shadow-sm border border-gray-100 items-center">
            <img :src="'{{ asset('storage') }}/' + item.image_path" :alt="item.name" class="w-20 h-20 object-cover rounded-2xl bg-gray-50">
            <div class="flex-1">
              <h3 class="font-black text-gray-900 text-base leading-tight mb-1" x-text="item.name"></h3>
              <!-- Variant badges -->
              <div x-show="item.selectedColor || item.selectedSize" class="flex gap-1.5 mb-1.5 flex-wrap">
                <span x-show="item.selectedColor" class="bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-purple-100" x-text="item.selectedColor"></span>
                <span x-show="item.selectedSize" class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-md border border-blue-100" x-text="'Size: ' + item.selectedSize"></span>
              </div>
              <p class="text-mango font-black text-sm mb-2">
                <span x-show="item.isBundle">Bundle Price: </span>
                Rs.<span x-text="(item.price * item.quantity).toLocaleString()"></span>
              </p>
              <template x-if="!item.isBundle">
                <div class="flex items-center gap-3 bg-gray-50 w-max rounded-xl p-1 border border-gray-100">
                  <button @click="updateQuantity(index, -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-white rounded-lg transition shadow-sm bg-gray-100">-</button>
                  <span class="font-black w-4 text-center text-sm" x-text="item.quantity"></span>
                  <button @click="updateQuantity(index, 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-white rounded-lg transition shadow-sm bg-gray-100">+</button>
                </div>
              </template>
              <template x-if="item.isBundle">
                <div class="flex items-center justify-between gap-3 bg-wildOrchid/10 w-max rounded-xl px-3 py-1 border border-wildOrchid/20 mt-1">
                  <span class="text-wildOrchid font-black text-sm" x-text="item.quantity + ' pcs Bundle'"></span>
                  <button @click="cart.splice(index, 1); if(cart.length===0) cartOpen=false;" class="text-red-500 hover:text-red-700 ml-2 p-1 bg-white rounded-md shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </template>
            </div>
          </div>
        </template>

        <!-- Quick Add Product -->
        <div x-show="cart.length > 0" class="mt-4">
          <a href="{{ url('/#shop') }}" class="w-full bg-white border-2 border-dashed border-gray-200 text-gray-500 font-bold py-3 rounded-2xl flex items-center justify-center gap-2 hover:bg-gray-50 hover:text-gray-900 transition-colors active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Add more products
          </a>
        </div>

        <div x-show="cart.length > 0" class="mt-8 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
          <h3 class="font-black text-lg text-gray-900 mb-4">Delivery Details</h3>
          <div class="space-y-4">
            <input type="text" x-model="customer.name" placeholder="Full Name" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-medium py-3">
            <input type="tel" x-model="customer.phone" placeholder="Phone Number" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-medium py-3">
            <textarea x-model="customer.address" placeholder="Full Delivery Address" rows="2" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-medium py-3 resize-none"></textarea>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Delivery Location</label>
              <select x-model="customer.delivery_location" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-bold py-3 text-gray-900 cursor-pointer">
                <option value="inside">Inside Kathmandu Valley (+ Rs. {{ setting('delivery_charge_inside', 50) }})</option>
                <option value="outside">Outside Kathmandu Valley (+ Rs. {{ setting('delivery_charge_outside', 100) }})</option>
              </select>
            </div>
            <p x-show="formError" x-text="formError" class="text-red-500 text-sm font-bold bg-red-50 p-3 rounded-xl border border-red-100"></p>
          </div>
        </div>
      </div>

      <div x-show="cart.length > 0" class="p-6 bg-white border-t border-gray-100 mt-auto shadow-[0_-10px_30px_rgba(0,0,0,0.05)]">
        <div class="flex justify-between items-end mb-2">
          <span class="text-gray-500 font-bold">Items Total</span>
          <span class="text-lg font-bold text-gray-900">Rs.<span x-text="itemsTotal.toLocaleString()"></span></span>
        </div>
        <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-4">
          <span class="text-gray-500 font-bold">Delivery Fee</span>
          <span class="text-lg font-bold text-gray-900">Rs.<span x-text="deliveryCharge.toLocaleString()"></span></span>
        </div>
        <div class="flex justify-between items-end mb-6">
          <span class="text-gray-500 font-black">Grand Total</span>
          <span class="text-3xl font-black text-gray-900">Rs.<span x-text="cartTotal.toLocaleString()"></span></span>
        </div>
        <button @click="placeOrder()" :disabled="isSubmitting" class="w-full bg-gray-900 text-white font-black py-4 rounded-xl text-lg hover:bg-gray-800 active:scale-95 transition-all shadow-xl shadow-gray-900/20 disabled:opacity-70 disabled:cursor-not-allowed">
          <span x-text="isSubmitting ? 'Processing...' : 'Place Order (COD)'"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Celebration Overlay -->
  <div x-show="showCelebration" x-transition.opacity.duration.500ms class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/90 backdrop-blur-md" x-cloak>
    <div class="text-center p-8 bg-white rounded-[3rem] max-w-sm mx-4 shadow-2xl transform scale-110">
      <h2 class="text-3xl font-black text-gray-900 mb-2">Order Received!</h2>
      <p class="text-gray-500 font-medium">We'll call you shortly.</p>
      <a href="{{ route('home') }}" class="mt-6 inline-block bg-mango text-gray-900 font-bold px-6 py-2 rounded-lg">Back to Shop</a>
    </div>
  </div>

  <script>
    function shopData() {
      return {
        products: @json($products),
        cart: JSON.parse(localStorage.getItem('cart') || '[]'),
        cartOpen: false,
        mobileMenuOpen: false,
        
        customer: { name: '', phone: '', address: '', delivery_location: 'inside' },
        formError: '',
        isSubmitting: false,
        showCelebration: false,
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
          this.$watch('cart', val => localStorage.setItem('cart', JSON.stringify(val)));
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

        needsVariants(product) {
          const cat = product.category;
          if (!cat) return false;
          return (cat.has_color_variants && cat.color_options && cat.color_options.length > 0) ||
              (cat.has_size_variants && cat.size_options && cat.size_options.length > 0);
        },

        triggerAddToCart(product) {
          // Bundle-only card: skip modal, add directly using the selected bundle qty
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
          const cat = this.variantProduct.category;
          const needsColor = cat.has_color_variants && cat.color_options && cat.color_options.length > 0;
          const needsSize = cat.has_size_variants && cat.size_options && cat.size_options.length > 0;
          if ((needsColor && !this.selectedColor) || (needsSize && !this.selectedSize)) {
            this.variantError = true;
            return;
          }
          this.variantModalOpen = false;
          this.processAddToCart(
            this.variantProduct, this.pendingVariantQty, this.pendingVariantPrice,
            this.pendingVariantIsBundle, this.selectedColor, this.selectedSize
          );
        },

        processAddToCart(product, qty, unitPrice, isBundle, color = '', size = '') {
          const cartItemId = `${product.id}_${qty}_${color}_${size}`;
          const existing = this.cart.find(i => i.cartItemId === cartItemId);
          if (existing) {
            existing.quantity += qty;
          } else {
            this.cart.push({ 
              ...product, quantity: qty, price: unitPrice, isBundle: isBundle, 
              cartItemId: cartItemId, selectedColor: color || null, selectedSize: size || null
            });
          }
          this.bundleSelectionOpen = false;
          this.cartOpen = true;

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
          }
          else alert("Your cart is empty.");
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
          if (!this.customer.name || !this.customer.phone || !this.customer.address) {
            this.formError = 'Please fill out all delivery details.';
            return;
          }
          this.formError = '';
          this.isSubmitting = true;

          try {
            const response = await fetch('{{ url('checkout') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
              },
              body: JSON.stringify({
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
              })
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

              this.cartOpen = false;
              this.showCelebration = true;
              setTimeout(() => {
                this.showCelebration = false;
                this.cart = [];
                this.customer = { name: '', phone: '', address: '', delivery_location: 'inside' };
                this.isSubmitting = false;
              }, 4000);
            } else {
              const errorData = await response.json();
              this.formError = errorData.message || 'Error placing order.';
              this.isSubmitting = false;
            }
          } catch (error) {
            this.formError = 'Network error. Please try again.';
            this.isSubmitting = false;
          }
        }
      }
    }
  </script>
</body>
</html>
