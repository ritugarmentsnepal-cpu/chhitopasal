<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Flash Sales | {{ setting('store_name', 'Chhito Pasal') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-white shadow-sm py-2 hidden md:block">
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
                <a href="{{ route('home') }}" class="px-4 py-1.5 rounded-full transition-colors text-sm font-bold text-gray-600 hover:text-primary">Home</a>
                <a href="{{ route('frontend.flash-sales') }}" class="px-4 py-1.5 rounded-full transition-colors text-sm font-bold bg-primary text-white">Flash Sales</a>
                <a href="{{ route('company.profile') }}" class="transition-colors text-sm text-gray-600 hover:text-primary">Company</a>
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                <div class="relative hidden md:block">
                    <input type="text" x-model="searchQuery" placeholder="Search..." class="border-transparent rounded-full py-2 pl-10 pr-4 focus:bg-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm w-64 bg-gray-100 text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-4 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                
                <button @click="mobileSearchOpen = !mobileSearchOpen" class="md:hidden transition-colors text-gray-600 hover:text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </button>

                <button @click="toggleCart()" class="relative transition-colors text-gray-600 hover:text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" x-transition class="absolute -top-2 -right-2 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center border border-white bg-primary"></span>
                </button>
            </div>
        </div>

        <!-- Mobile Search -->
        <div x-show="mobileSearchOpen" x-collapse class="md:hidden px-4 py-3 border-t bg-white border-gray-100">
            <div class="relative">
                <input type="text" x-model="searchQuery" placeholder="Search..." class="w-full border-transparent rounded-full py-2 pl-10 pr-4 focus:bg-white focus:ring-2 focus:ring-primary text-sm bg-gray-100 text-gray-900" x-ref="mobileSearch" @keydown.escape="mobileSearchOpen = false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-4 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>
    </header>

    <!-- Mobile App Header -->
    <header class="md:hidden fixed top-0 left-0 right-0 z-50 bg-white shadow-sm pb-3 pt-safe">
        <div class="px-4 py-2 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                @if(setting('store_logo'))
                    <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-7 w-auto object-contain">
                @else
                    <span class="text-xl font-bold font-display text-gray-900">
                        {{ setting('store_name', 'Chhito Pasal') }}
                    </span>
                @endif
            </a>
            <div class="flex items-center gap-3">
                <button @click="toggleCart()" class="relative text-gray-800 bg-gray-50 p-2 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" class="absolute -top-1 -right-1 w-4 h-4 bg-[#FF4C4C] text-white rounded-full border border-white text-[9px] font-black flex items-center justify-center"></span>
                </button>
            </div>
        </div>
        <!-- Persistent Search Bar -->
        <div class="px-4 mt-1">
            <div class="relative">
                <input type="text" x-model="searchQuery" placeholder="Search for products..." class="w-full border-gray-100 rounded-xl py-2 pl-10 pr-4 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-[#FF4C4C] focus:border-transparent text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>
    </header>


    <!-- Flash Sales Header Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-40 md:mt-40 mb-8 relative z-20">
        <!-- Formal Banner -->
        <div class="bg-gray-900 border border-gray-800 p-8 sm:p-16 text-white flex flex-col items-center text-center relative shadow-2xl overflow-hidden">
            <!-- Subtle elegant pattern/texture -->
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] mix-blend-overlay pointer-events-none"></div>
            
            <div class="relative z-10 mx-auto max-w-2xl flex flex-col items-center">
                <div class="flex items-center gap-4 mb-6">
                    <div class="h-[1px] w-12 bg-red-500"></div>
                    <span class="text-red-400 text-xs font-bold uppercase tracking-[0.2em]">Exclusive Event</span>
                    <div class="h-[1px] w-12 bg-red-500"></div>
                </div>
                <h1 class="text-4xl sm:text-6xl font-display font-light tracking-tight mb-4">Flash <span class="font-bold text-white">Sales</span></h1>
                <p class="text-gray-400 text-base sm:text-lg font-light leading-relaxed">Discover exclusive discounts on premium products. Carefully curated, available for a strictly limited time.</p>
            </div>
        </div>
    </section>

    <!-- Flash Sales Grid -->
    <main id="shop" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-24">
        
        <div class="flex justify-between items-end mb-8 border-b border-gray-200 pb-4">
            <h2 class="text-2xl font-display font-bold text-gray-900">All Deals</h2>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-500">{{ $flashSaleProducts->count() }} Products</span>
            </div>
        </div>

        @if($flashSaleProducts->count() === 0)
        <!-- Empty State -->
        <div class="text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">⏳</span>
            </div>
            <h3 class="text-lg font-display font-bold text-gray-900 mb-1">No Flash Sales Right Now</h3>
            <p class="text-gray-500 text-sm mb-4">Check back later for exciting new deals and discounts.</p>
            <a href="{{ route('home') }}" class="text-primary font-bold text-sm hover:underline">Continue Shopping</a>
        </div>
        @else
        <!-- Product Grid -->
        <!-- Product Grid (Formal) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-gray-200 border border-gray-200 mt-8 shadow-sm">
            @foreach($flashSaleProducts as $product)
                <div class="bg-white p-6 relative group cursor-pointer hover:bg-gray-50 transition-colors duration-500" @click="window.location.href = '{{ url('product') }}/{{ $product->parent_product_slug ?: $product->slug }}{{ isset($product->bundle_qty) ? '?bundle=' . $product->bundle_qty : '' }}'">
                    <div class="absolute top-4 left-4 z-10">
                        <span class="bg-gray-900 text-white text-[9px] font-bold uppercase tracking-widest px-3 py-1">Limited</span>
                    </div>
                    
                    <div class="aspect-[4/5] bg-transparent mb-6 overflow-hidden relative flex items-center justify-center">
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-700 ease-in-out" loading="lazy">
                    </div>
                    
                    <div class="flex flex-col text-center">
                        <h3 class="font-bold text-gray-900 text-xs sm:text-sm uppercase tracking-wide line-clamp-1 mb-2">{{ $product->name }}</h3>
                        <p class="text-gray-400 text-[10px] sm:text-xs font-medium uppercase tracking-wider line-clamp-1 mb-3">{{ $product->category ? $product->category->name : 'Accessories' }}</p>
                        <div class="flex justify-center items-center gap-3">
                            <span class="text-[10px] sm:text-xs text-gray-400 line-through tracking-wider">Rs.{{ number_format($product->original_price) }}</span>
                            <span class="font-bold text-red-600 text-sm sm:text-base tracking-wider">Rs.{{ number_format($product->price) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer id="about" class="hidden md:block bg-white border-t border-gray-100 pt-10 md:pt-16 pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                        <li><a href="{{ route('home') }}" class="hover:text-primary transition-colors">Home</a></li>
                        <li><a href="{{ route('frontend.flash-sales') }}" class="hover:text-primary transition-colors">Flash Sales</a></li>
                        <li><a href="{{ route('privacy.policy') }}" class="hover:text-primary transition-colors">Privacy Policy</a></li>
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

            <!-- Desktop Copyright -->
            <div class="hidden md:flex border-t border-gray-100 pt-8 flex-col md:flex-row items-center justify-between text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} {{ setting('store_name', 'Chhito Pasal') }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
