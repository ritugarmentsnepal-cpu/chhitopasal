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

    {{-- Microsoft Clarity Analytics --}}
    @if(setting('microsoft_clarity_id'))
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y)})(window,document,"clarity","script","{{ setting('microsoft_clarity_id') }}");
    </script>
    @endif
</head>
<body class="antialiased text-gray-900 overflow-x-hidden selection:bg-wildOrchid selection:text-white"
      x-data="shopData()">

    <!-- 1. Header — compact mobile (52px), full desktop -->
    <header class="cp-header fixed top-0 left-0 right-0 z-40" :class="scrolled && 'scrolled'">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-full">

            <!-- Brand -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 group flex-shrink-0 min-w-0">
                @if(setting('store_logo'))
                    <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-8 md:h-9 w-auto object-contain max-w-[120px] md:max-w-[140px] mix-blend-multiply">
                @endif
                @php
                    $brandName = setting('store_name', 'Chhito Pasal');
                    $brandParts = explode(' ', $brandName, 2);
                @endphp
                <span class="text-[15px] md:text-lg font-black tracking-tight leading-none">
                    <span class="text-mango">{{ $brandParts[0] }}</span><span class="text-ink">{{ isset($brandParts[1]) ? ' '.$brandParts[1] : '' }}</span>
                </span>
            </a>

            <!-- Desktop Nav & Search -->
            <div class="hidden md:flex items-center flex-1 max-w-2xl px-12 gap-8">
                <nav class="flex gap-6 font-bold text-txt-secondary">
                    <a href="#" class="hover:text-ink transition-colors">Home</a>
                    <a href="#shop" class="hover:text-ink transition-colors">Shop</a>
                    <a href="#about" class="hover:text-ink transition-colors">About Us</a>
                </nav>
                <div class="flex-1 relative group">
                    <input type="text" x-model="searchQuery" placeholder="Search products..." class="w-full bg-gray-100/80 border-transparent rounded-full py-2.5 pl-12 pr-4 focus:bg-white focus:ring-2 focus:ring-mango focus:border-transparent transition-all shadow-inner font-medium placeholder-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-4 top-3 text-gray-400 group-focus-within:text-mango transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2">
                <!-- Mobile Search Toggle -->
                <button @click="mobileSearchOpen = !mobileSearchOpen" class="md:hidden p-2 rounded-xl text-txt-secondary active:scale-95 transition-transform hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </button>
                <!-- Desktop Cart Button -->
                <button @click="toggleCart()" class="relative bg-ink text-white p-3 rounded-xl hover:bg-ink-light transition active:scale-95 shadow-btn hidden md:flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span class="font-bold">Cart</span>
                    <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" x-transition class="absolute -top-2 -right-2 bg-wildOrchid text-white text-xs font-black w-6 h-6 rounded-full flex items-center justify-center border-2 border-white shadow-sm"></span>
                </button>
            </div>
        </div>

        <!-- Mobile Expandable Search Bar -->
        <div x-show="mobileSearchOpen" x-collapse class="md:hidden border-t border-divider bg-white px-4 py-2">
            <div class="relative">
                <input type="text" x-model="searchQuery" placeholder="Search products..." class="w-full bg-softPearl border-border rounded-xl py-2.5 pl-10 pr-4 focus:ring-2 focus:ring-mango focus:border-transparent font-medium text-sm placeholder-txt-tertiary" x-ref="mobileSearch" @keydown.escape="mobileSearchOpen = false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-3 text-txt-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>
    </header>

    <!-- 2. Hero Section -->
    <section class="pt-[56px] md:pt-[80px] pb-3 sm:pb-8 px-3 sm:px-6 lg:px-8 max-w-[1600px] mx-auto">
        <!-- Mobile Hero: Clean gradient, no photo noise -->
        <div class="md:hidden rounded-2xl overflow-hidden relative" style="background: linear-gradient(135deg, #0A0F1E 0%, #1a1040 50%, #0A0F1E 100%);">
            <!-- Decorative mango glow -->
            <div class="absolute top-0 right-0 w-40 h-40 rounded-full opacity-30" style="background: radial-gradient(circle, #FFB627 0%, transparent 70%); filter: blur(30px);"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full opacity-15" style="background: radial-gradient(circle, #FF3366 0%, transparent 70%); filter: blur(25px);"></div>
            
            <div class="relative z-10 px-5 py-6">
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-mango/20 text-mango font-bold text-[11px] uppercase tracking-wider mb-3 border border-mango/30">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
                    Flash Sale
                </span>
                <h2 class="text-[22px] font-black text-white leading-[1.15] mb-2">{!! nl2br(e(setting('hero_title', 'Upgrade your lifestyle.'))) !!}</h2>
                <p class="text-gray-400 text-[13px] font-medium mb-4 leading-relaxed line-clamp-2">{{ setting('hero_subtitle', 'Discover the best tech, fashion, and home accessories delivered straight to your door.') }}</p>
                <a href="#shop" class="inline-flex items-center justify-center bg-mango text-ink font-black px-5 py-2.5 rounded-full active:scale-95 transition shadow-lg shadow-mango/25 text-sm group">
                    {{ setting('hero_cta', 'Shop Now') }}
                    <svg class="w-4 h-4 ml-1.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>

        <!-- Desktop Hero: Full photo banner -->
        <div class="hidden md:flex bg-ink rounded-[2rem] overflow-hidden relative shadow-xl flex-row items-center min-h-[360px]">
            <div class="absolute inset-0 bg-gradient-to-r from-ink via-ink/90 to-transparent z-10"></div>
            @if(setting('hero_image'))
                <img src="{{ asset('storage/' . setting('hero_image')) }}" class="absolute inset-0 w-full h-full object-cover" alt="Hero">
            @else
                <img src="https://images.unsplash.com/photo-1606813907291-d86efa9b94db?q=80&w=2074&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover" alt="Hero" loading="lazy">
            @endif
            <div class="relative z-20 p-16 md:p-24 md:w-2/3 lg:w-1/2">
                <span class="inline-flex items-center gap-2 py-1.5 px-4 rounded-full bg-mango text-ink font-black text-xs uppercase tracking-widest mb-6">⚡ Flash Sale</span>
                <h2 class="text-5xl md:text-6xl font-black text-white leading-[1.1] mb-6">{!! nl2br(e(setting('hero_title', 'Upgrade your lifestyle.'))) !!}</h2>
                <p class="text-gray-300 text-lg md:text-xl font-medium mb-10 max-w-lg">{{ setting('hero_subtitle', 'Discover the best tech, fashion, and home accessories delivered straight to your door. No hassle, just shopping.') }}</p>
                <a href="#shop" class="inline-flex items-center justify-center bg-mango text-ink font-black px-8 py-4 rounded-full hover:bg-amber-400 active:scale-95 transition shadow-xl shadow-mango/30 group text-base">
                    {{ setting('hero_cta', 'Shop Collection') }}
                    <svg class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- 3. Category Pills -->
    <section id="shop" class="sticky top-[52px] md:top-[72px] z-30 bg-softPearl/95 backdrop-blur-lg py-2.5 md:py-4">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-2 sm:gap-3 overflow-x-auto no-scrollbar pb-1">
                <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-ink text-white shadow-lg' : 'bg-white text-txt-secondary border-border hover:border-gray-300 hover:bg-gray-50'" class="cp-pill px-4 md:px-6 py-2.5 md:py-2.5 text-[13px] md:text-base border active:scale-95">
                    All Products
                </button>
                @foreach($categories as $category)
                    <button @click="activeCategory = '{{ $category->slug }}'" :class="activeCategory === '{{ $category->slug }}' ? 'bg-ink text-white shadow-lg' : 'bg-white text-txt-secondary border-border hover:border-gray-300 hover:bg-gray-50'" class="cp-pill px-4 md:px-6 py-2.5 md:py-2.5 text-[13px] md:text-base border active:scale-95">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 4. Product Grid -->
    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 cp-main-content pt-4 md:pt-6">
        
        <!-- Empty State -->
        <div x-show="filteredProducts.length === 0" x-cloak class="text-center py-16">
            <div class="w-20 h-20 bg-divider rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-txt-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <h3 class="text-xl font-black text-ink mb-1">No products found</h3>
            <p class="text-txt-secondary font-medium text-sm">Try adjusting your search or filter.</p>
            <button @click="searchQuery = ''; activeCategory = 'all'" class="mt-4 text-wildOrchid font-bold text-sm hover:underline">Clear all filters</button>
        </div>

        <!-- Product Grid: 2-col mobile, auto-fill desktop -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-6 stagger-grid">
            <template x-for="(product, index) in filteredProducts" :key="product.id">
                <article class="cp-card group relative flex flex-col cursor-pointer"
                         @click="window.location.href = '{{ url('product') }}/' + product.slug">
                    
                    <!-- Image Container -->
                    <div class="relative overflow-hidden card-shimmer aspect-[3/4] md:aspect-[4/5] bg-divider">
                        <img :src="'{{ asset('storage') }}/' + product.image_path" :alt="product.name" 
                             class="w-full h-full object-cover transition-transform duration-700 md:group-hover:scale-105"
                             loading="lazy">
                        
                        <!-- Stock badge: dot-only on mobile, text on desktop -->
                        <div class="absolute top-2 right-2 md:top-3 md:right-3 z-10">
                            <!-- Mobile: dot only -->
                            <span class="md:hidden w-2.5 h-2.5 rounded-full block border border-white"
                                  :class="product.in_stock ? 'bg-success shadow-[0_0_6px_rgba(0,196,140,0.6)]' : 'bg-wildOrchid'"></span>
                            <!-- Desktop: pill -->
                            <div class="hidden md:inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-[10px] font-bold backdrop-blur-md"
                                 :class="product.in_stock ? 'bg-success-soft/90 text-success border border-success/20' : 'bg-red-50/90 text-wildOrchid border border-wildOrchid/20'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="product.in_stock ? 'bg-success' : 'bg-wildOrchid'"></span>
                                <span x-text="product.in_stock ? 'In Stock' : 'Sold Out'"></span>
                            </div>
                        </div>

                        <!-- Desktop-only hover action buttons -->
                        <div class="card-actions-desktop absolute hidden md:flex gap-2 bottom-3 left-3 right-3 z-10">
                            <button @click.stop="if(window.innerWidth < 768) { window.location.href = '{{ url('product') }}/' + product.slug; return; } openQuickView(product)" 
                                    class="flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs font-bold bg-white/93 backdrop-blur-xl text-ink border border-white/50 shadow-lg transition hover:bg-white">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                Quick View
                            </button>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="p-3 md:p-4 flex flex-col flex-1">
                        <!-- Product Name -->
                        <h3 class="font-bold text-[13px] md:text-[15px] text-ink leading-snug mb-2 line-clamp-1 md:line-clamp-2" x-text="product.name"></h3>
                        
                        <div class="flex-1"></div>

                        <!-- Price + Buy row -->
                        <div class="flex items-center justify-between mt-auto pt-2 md:pt-3 border-t border-divider">
                            <div>
                                <div class="flex items-baseline gap-0.5">
                                    <span class="text-[11px] md:text-[13px] font-bold text-mango">Rs.</span>
                                    <span class="text-base md:text-xl font-black text-ink tracking-tight" x-text="product.price.toLocaleString()"></span>
                                </div>
                            </div>
                            <button @click.stop.prevent="triggerAddToCart(product)" :disabled="!product.in_stock" 
                                    class="cp-btn-buy rounded-lg md:rounded-xl text-[11px] md:text-sm py-2 px-3 md:py-2.5 md:px-5">
                                <span x-text="product.in_stock ? 'Buy' : 'Sold'"></span>
                            </button>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </main>

    <!-- 5. Footer -->
    <footer id="about" class="bg-ink text-white pb-24 md:pb-8 rounded-t-xl sm:rounded-t-[2rem] mt-6 md:mt-10 pt-10 sm:pt-20">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-12 mb-10 sm:mb-16">
                <div>
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 mb-6">
                        @if(setting('store_logo'))
                            <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-9 w-auto object-contain brightness-0 invert max-w-[120px]">
                        @endif
                        @php
                            $footerBrandParts = explode(' ', setting('store_name', 'Chhito Pasal'), 2);
                        @endphp
                        <span class="text-lg font-black tracking-tight leading-none">
                            <span class="text-mango">{{ $footerBrandParts[0] }}</span><span class="text-white">{{ isset($footerBrandParts[1]) ? ' '.$footerBrandParts[1] : '' }}</span>
                        </span>
                    </a>
                    <p class="text-gray-400 font-medium max-w-sm mb-6 leading-relaxed">Your premium destination for the finest products. Delivering happiness across the country with lightning speed.</p>
                    <div class="flex gap-4">
                        <a href="{{ setting('facebook_url', '#') }}" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:bg-mango hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="{{ setting('instagram_url', '#') }}" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:bg-mango hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="{{ setting('tiktok_url', '#') }}" target="_blank" rel="noopener" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:bg-mango hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.52a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 0010.86 4.48V13.2a8.16 8.16 0 005.58 2.19V12a4.85 4.85 0 01-5.58-2.12V6.69h5.58z"/></svg>
                        </a>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-8 md:border-t-0 md:pt-0">
                    <h4 class="font-black text-lg mb-5">Quick Links</h4>
                    <ul class="space-y-3 text-gray-400 font-medium">
                        <li><a href="#" class="hover:text-mango transition-colors">Home</a></li>
                        <li><a href="#shop" class="hover:text-mango transition-colors">Shop</a></li>
                        <li><a href="#" class="hover:text-mango transition-colors">Categories</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-mango transition-colors">Staff Login</a></li>
                    </ul>
                </div>
                <div class="border-t border-gray-800 pt-8 md:border-t-0 md:pt-0">
                    <h4 class="font-black text-lg mb-5">Support</h4>
                    <ul class="space-y-3 text-gray-400 font-medium">
                        <li><a href="mailto:{{ setting('contact_email', 'support@chhitopasal.com') }}" class="hover:text-mango transition-colors">{{ setting('contact_email', 'support@chhitopasal.com') }}</a></li>
                        <li><a href="tel:{{ setting('contact_phone', '+977 9800000000') }}" class="hover:text-mango transition-colors">{{ setting('contact_phone', '+977 9800000000') }}</a></li>
                        <li><a href="#" class="hover:text-mango transition-colors cursor-default">{{ setting('contact_address', 'Kathmandu, Nepal') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row items-center justify-between text-gray-500 font-medium text-sm gap-2">
                <p>&copy; {{ date('Y') }} {{ setting('store_name', 'Chhito Pasal') }}. All rights reserved.</p>
                <p>Crafted with ❤️ for premium shopping.</p>
            </div>
        </div>
    </footer>

    <!-- 6. Product Quick View Modal -->
    <div x-show="quickViewOpen" x-cloak class="fixed inset-0 z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="quickViewOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeQuickView()"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="quickViewOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl flex flex-col md:flex-row">
                    
                    <!-- Close Button -->
                    <button @click="closeQuickView()" class="absolute top-4 right-4 z-10 w-10 h-10 bg-white/80 backdrop-blur rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>

                    <!-- Modal Content -->
                    <template x-if="selectedProduct">
                        <div class="flex flex-col md:flex-row w-full">
                            <!-- Image Half (Gallery) -->
                            <div class="w-full md:w-1/2 bg-gray-50 p-6 sm:p-10 flex flex-col items-center justify-center">
                                <div class="w-full aspect-[4/5] bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 flex items-center justify-center mb-4">
                                    <template x-if="!quickViewIsVideo">
                                        <img :src="quickViewMedia" :alt="selectedProduct.name" class="w-full h-full object-contain drop-shadow-md mix-blend-multiply p-4">
                                    </template>
                                    <template x-if="quickViewIsVideo">
                                        <video :src="quickViewMedia" controls autoplay muted class="w-full h-full object-contain bg-black"></video>
                                    </template>
                                </div>

                                <!-- Thumbnails -->
                                <div class="flex gap-3 overflow-x-auto no-scrollbar w-full py-2">
                                    <button @click="quickViewMedia = '{{ asset('storage') }}/' + selectedProduct.image_path; quickViewIsVideo = false" 
                                            :class="quickViewMedia === '{{ asset('storage') }}/' + selectedProduct.image_path ? 'ring-2 ring-mango border-transparent' : 'border-gray-200 opacity-70 hover:opacity-100'"
                                            class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 transition-all active:scale-95 bg-white">
                                        <img :src="'{{ asset('storage') }}/' + selectedProduct.image_path" class="w-full h-full object-cover">
                                    </button>

                                    <template x-if="selectedProduct.additional_images && selectedProduct.additional_images.length > 0">
                                        <template x-for="img in selectedProduct.additional_images">
                                            <button @click="quickViewMedia = '{{ asset('storage') }}/' + img; quickViewIsVideo = false" 
                                                    :class="quickViewMedia === '{{ asset('storage') }}/' + img ? 'ring-2 ring-mango border-transparent' : 'border-gray-200 opacity-70 hover:opacity-100'"
                                                    class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 transition-all active:scale-95 bg-white">
                                                <img :src="'{{ asset('storage') }}/' + img" class="w-full h-full object-cover">
                                            </button>
                                        </template>
                                    </template>

                                    <template x-if="selectedProduct.video_path">
                                        <button @click="quickViewMedia = '{{ asset('storage') }}/' + selectedProduct.video_path; quickViewIsVideo = true" 
                                                :class="quickViewIsVideo ? 'ring-2 ring-mango border-transparent' : 'border-gray-200 opacity-70 hover:opacity-100'"
                                                class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 transition-all active:scale-95 bg-gray-900 flex items-center justify-center text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Details Half -->
                            <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col">
                                <span class="text-xs font-black uppercase tracking-wider text-wildOrchid mb-3" x-text="selectedProduct.category?.name || 'Uncategorized'"></span>
                                <h2 class="text-3xl sm:text-4xl font-black text-gray-900 mb-6 leading-tight" x-text="selectedProduct.name"></h2>
                                
                                <!-- Dominant Price Block -->
                                <div class="bg-gray-50 border border-mango/30 rounded-[2rem] p-6 mb-8 shadow-[0_10px_30px_rgba(255,209,102,0.15)] relative overflow-hidden">
                                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-mango/20 rounded-full blur-3xl"></div>
                                    <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mb-1 relative z-10">Our Price</p>
                                    <div class="flex items-baseline gap-2 relative z-10">
                                        <span class="text-xl md:text-3xl font-bold text-mango">Rs.</span>
                                        <span class="text-5xl md:text-[4rem] font-black text-gray-900 tracking-tighter leading-none" x-text="selectedProduct.price.toLocaleString()"></span>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-2xl p-5 mb-8 border border-gray-100">
                                    <h4 class="font-bold text-gray-900 mb-2">Description</h4>
                                    <p class="text-gray-600 font-medium leading-relaxed" x-text="selectedProduct.description"></p>
                                </div>
                                
                                <div class="flex items-center gap-4 text-sm font-bold text-gray-600 mb-8">
                                    <div :class="selectedProduct.in_stock ? 'bg-green-50 text-green-700 border-green-100' : 'bg-red-50 text-red-700 border-red-100'" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        <span x-text="selectedProduct.in_stock ? 'In Stock' : 'Out of Stock'"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 bg-gray-100 px-3 py-1.5 rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM5.94 12.06a1 1 0 010-1.41l4-4a1 1 0 011.41 0l4 4a1 1 0 01-1.41 1.41L11 9.41V15a1 1 0 11-2 0V9.41L6.65 11.76a1 1 0 01-1.41 0z" clip-rule="evenodd" /></svg>
                                        <span x-text="selectedProduct.weight_grams + 'g'"></span>
                                    </div>
                                </div>
                                
                                <button @click="triggerAddToCart(selectedProduct); closeQuickView();" class="mt-auto w-full bg-gray-900 text-white font-black py-4 px-8 rounded-2xl hover:bg-gray-800 active:scale-95 transition-all shadow-xl shadow-gray-900/20 flex items-center justify-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                    Buy Now
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
        <div x-show="bundleSelectionOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="bundleSelectionOpen = false"></div>
        <div x-show="bundleSelectionOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-3xl p-6 sm:p-8 w-full shadow-2xl z-10 mx-auto" style="max-width: 450px;">
            <button @click="bundleSelectionOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-900 bg-gray-50 p-2 rounded-full"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Choose Package</h3>
            <p class="text-gray-500 mb-6 font-medium">Select a bundle to save more!</p>
            
            <div class="space-y-3">
                <!-- Single Piece -->
                <button @click="bundleSelectionOpen = false; if(needsVariants(bundleProduct)) { openVariantModal(bundleProduct, 1, bundleProduct.price, false); } else { processAddToCart(bundleProduct, 1, bundleProduct.price, false, '', ''); }" class="w-full border-2 border-gray-200 rounded-2xl p-4 flex justify-between items-center hover:border-mango hover:bg-mango/5 transition text-left group">
                    <div>
                        <span class="block font-black text-gray-900 text-lg">Single Piece</span>
                        <span class="block text-gray-500 text-sm">Standard price</span>
                    </div>
                    <span class="font-black text-xl text-gray-900 group-hover:text-mango transition">Rs.<span x-text="bundleProduct?.price.toLocaleString()"></span></span>
                </button>
                
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

            <!-- Color Selection (product-level) -->
            <template x-if="variantProduct?.color_options?.length > 0">
                <div class="mb-6">
                    <label class="block text-sm font-black text-gray-700 mb-3 uppercase tracking-wider">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/></svg>
                            Color
                        </span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="color in variantProduct.color_options" :key="color">
                            <button type="button" @click="selectedColor = color" 
                                :class="selectedColor === color ? 'border-gray-900 bg-gray-900 text-white shadow-lg scale-105' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400'" 
                                class="px-4 py-2 rounded-xl border-2 font-bold text-sm transition-all duration-200 active:scale-95"
                                x-text="color">
                            </button>
                        </template>
                    </div>
                    <p x-show="variantError && !selectedColor" class="text-red-500 text-xs font-bold mt-2">Please select a color</p>
                </div>
            </template>

            <!-- Size Selection (product-level) -->
            <template x-if="variantProduct?.size_options?.length > 0">
                <div class="mb-6">
                    <label class="block text-sm font-black text-gray-700 mb-3 uppercase tracking-wider">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            Size
                        </span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="size in variantProduct.size_options" :key="size">
                            <button type="button" @click="selectedSize = size" 
                                :class="selectedSize === size ? 'border-gray-900 bg-gray-900 text-white shadow-lg scale-105' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-400'" 
                                class="w-14 h-14 rounded-xl border-2 font-black text-sm transition-all duration-200 active:scale-95 flex items-center justify-center"
                                x-text="size">
                            </button>
                        </template>
                    </div>
                    <p x-show="variantError && !selectedSize" class="text-red-500 text-xs font-bold mt-2">Please select a size</p>
                </div>
            </template>

            <!-- Add to Cart Button -->
            <button @click="confirmVariantAddToCart()" class="w-full bg-gray-900 text-white font-black py-4 rounded-2xl hover:bg-gray-800 active:scale-95 transition-all shadow-xl shadow-gray-900/20 flex items-center justify-center gap-3 mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                Add to Cart
            </button>
        </div>
    </div>

    <!-- Bottom Navigation Bar (Mobile only) -->
    <nav class="cp-bottom-nav fixed bottom-0 left-0 right-0 z-40 md:hidden">
        <div class="flex items-center justify-around h-[60px]">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 text-mango">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                <span class="text-[10px] font-bold">Home</span>
            </a>
            <a href="#shop" class="flex flex-col items-center gap-0.5 text-txt-secondary">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span class="text-[10px] font-bold">Shop</span>
            </a>
            <button @click="mobileSearchOpen = !mobileSearchOpen; $nextTick(() => { if(mobileSearchOpen) $refs.mobileSearch?.focus() })" class="flex flex-col items-center gap-0.5 text-txt-secondary">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span class="text-[10px] font-bold">Search</span>
            </button>
            <button @click="toggleCart()" class="flex flex-col items-center gap-0.5 text-txt-secondary relative">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="text-[10px] font-bold">Cart</span>
                <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" class="absolute -top-1 right-1 bg-wildOrchid text-white text-[8px] font-black min-w-[16px] h-[16px] rounded-full flex items-center justify-center"></span>
            </button>
        </div>
    </nav>

    <!-- The Cart Modal Overlay -->
    <div x-show="cartOpen" x-cloak class="fixed inset-0 z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="cartOpen" x-transition.opacity class="fixed inset-0 bg-ink/60 backdrop-blur-sm transition-opacity" @click="cartOpen = false"></div>

        <!-- The Drawer (Mobile: Full-screen slide up, Desktop: Side drawer) -->
        <div x-show="cartOpen" 
             x-transition:enter="transform transition ease-out duration-300" 
             x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full" 
             x-transition:enter-end="translate-y-0 md:translate-x-0" 
             x-transition:leave="transform transition ease-in duration-300" 
             x-transition:leave-start="translate-y-0 md:translate-x-0" 
             x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full" 
             class="cp-sheet fixed bottom-0 md:top-0 right-0 w-full md:w-[450px] h-[95vh] md:h-screen rounded-t-[20px] md:rounded-none md:rounded-l-[20px] flex flex-col">
            
            <!-- Grab handle (mobile) -->
            <div class="cp-grab-handle md:hidden"></div>

            <!-- Header -->
            <div class="px-5 py-4 md:px-6 md:py-6 border-b border-divider flex items-center justify-between">
                <h2 class="text-xl md:text-2xl font-black text-ink flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-mango" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    Your Cart
                </h2>
                <button @click="cartOpen = false" class="text-txt-tertiary hover:text-ink bg-divider p-2 rounded-full transition-colors active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Cart Content -->
            <div class="flex-1 overflow-y-auto p-6 bg-[#F8FAFC]">
                <template x-if="cart.length === 0">
                    <div class="text-center py-12 flex flex-col items-center justify-center h-full">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 mb-2">Your cart is empty</p>
                        <p class="text-gray-500 font-medium mb-6">Looks like you haven't added anything yet.</p>
                        <button @click="cartOpen = false" class="bg-gray-900 text-white font-bold py-3 px-6 rounded-full hover:bg-gray-800 transition shadow-lg">Start Shopping</button>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="index">
                    <div class="flex gap-3 mb-3 bg-white p-3 rounded-2xl shadow-sm border border-divider items-center">
                        <img :src="'{{ asset('storage') }}/' + item.image_path" :alt="item.name" class="w-14 h-14 md:w-20 md:h-20 object-cover rounded-xl bg-divider flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-black text-gray-900 text-sm md:text-base leading-tight mb-1 line-clamp-2" x-text="item.name"></h3>
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
                                    <button @click="updateQuantity(index, -1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-white rounded-lg transition shadow-sm bg-gray-100"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg></button>
                                    <span class="font-black w-4 text-center text-sm" x-text="item.quantity"></span>
                                    <button @click="updateQuantity(index, 1)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-white rounded-lg transition shadow-sm bg-gray-100"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></button>
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
                    <a href="#shop" @click="cartOpen = false" class="w-full bg-white border-2 border-dashed border-gray-200 text-gray-500 font-bold py-3 rounded-2xl flex items-center justify-center gap-2 hover:bg-gray-50 hover:text-gray-900 transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add more products
                    </a>
                </div>

                <!-- Order Form (Only shows if cart has items) -->
                <div x-show="cart.length > 0" class="mt-6 bg-white p-5 md:p-6 rounded-2xl border border-divider shadow-sm">
                    <h3 class="font-black text-base md:text-lg text-ink mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-wildOrchid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Delivery Details
                    </h3>
                    <div class="space-y-4">
                        <div class="cp-input-group">
                            <input type="text" x-model="customer.name" placeholder=" " id="cust-name" class="peer">
                            <label for="cust-name">Full Name</label>
                        </div>
                        <div class="cp-input-group">
                            <input type="tel" x-model="customer.phone" placeholder=" " id="cust-phone" class="peer">
                            <label for="cust-phone">Phone Number</label>
                        </div>
                        <div class="cp-input-group">
                            <textarea x-model="customer.address" placeholder=" " id="cust-address" rows="2" class="peer resize-none"></textarea>
                            <label for="cust-address">Full Delivery Address</label>
                        </div>
                        
                        <div class="pt-1">
                            <label class="block text-[10px] font-bold text-txt-tertiary mb-1.5 uppercase tracking-wider">Delivery Location</label>
                            <select x-model="customer.delivery_location" class="w-full bg-softPearl border-border rounded-xl focus:ring-2 focus:ring-mango focus:border-transparent font-bold py-3 text-ink cursor-pointer text-sm">
                                <option value="inside">Inside Kathmandu Valley (+ Rs. {{ setting('delivery_charge_inside', 50) }})</option>
                                <option value="outside">Outside Kathmandu Valley (+ Rs. {{ setting('delivery_charge_outside', 100) }})</option>
                            </select>
                        </div>

                        <p x-show="formError" x-text="formError" class="text-wildOrchid text-sm font-bold bg-red-50 p-3 rounded-xl border border-wildOrchid/20"></p>
                    </div>
                </div>
            </div>

            <!-- Footer (Total & Button) -->
            <div x-show="cart.length > 0" class="p-6 bg-white border-t border-gray-100 mt-auto shadow-[0_-10px_30px_rgba(0,0,0,0.05)] md:rounded-bl-[2rem]">
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
                
                <button @click="placeOrder()" :disabled="isSubmitting" class="w-full bg-gray-900 text-white font-black py-4 rounded-xl text-lg hover:bg-gray-800 active:scale-95 transition-all shadow-xl shadow-gray-900/20 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span x-show="!isSubmitting">Place Order (COD)</span>
                    <span x-show="isSubmitting">Processing...</span>
                    <svg x-show="!isSubmitting" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Celebration Overlay -->
    <div x-show="showCelebration" x-transition.opacity.duration.500ms class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/90 backdrop-blur-md">
        <div class="text-center p-8 bg-white rounded-[3rem] max-w-sm mx-4 shadow-2xl transform scale-110">
            <div class="w-24 h-24 bg-mango rounded-full flex items-center justify-center mx-auto mb-6 shadow-[0_0_40px_#FFD166] animate-bounce">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h2 class="text-3xl font-black text-gray-900 mb-2">Order Received!</h2>
            <p class="text-gray-500 font-medium">We'll call you within 5 minutes to confirm.</p>
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
                    // Load cart from localStorage
                    const savedCart = localStorage.getItem('cart');
                    if (savedCart) {
                        this.cart = JSON.parse(savedCart);
                    }
                    
                    this.$watch('cart', val => localStorage.setItem('cart', JSON.stringify(val)));
                    
                    window.addEventListener('scroll', () => {
                        this.scrolled = window.scrollY > 20;
                    });
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
                    if (product.bundles && product.bundles.length > 0) {
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
                    } else {
                        alert("Your cart is empty. Add some products first!");
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
