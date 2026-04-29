<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('store_name', 'Chhito Pasal') }} | Premium Store</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body { font-family: 'Outfit', sans-serif; background-color: #FDFFFC; }
        
        @keyframes staggeredFadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-staggered {
            animation: staggeredFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }
    </style>
</head>
<body class="antialiased text-gray-900 overflow-x-hidden selection:bg-wildOrchid selection:text-white"
      x-data="shopData()">

    <!-- 1. Header (Search, Menu, Cart) -->
    <header class="fixed top-0 left-0 right-0 z-40 transition-all duration-300" :class="scrolled ? 'bg-white/80 backdrop-blur-xl shadow-sm py-3' : 'bg-transparent py-5'">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            
            <!-- Mobile Menu Toggle -->
            <button @click="mobileMenuOpen = true" class="md:hidden p-2 -ml-2 text-gray-900 active:scale-95 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
            </button>

            <!-- Brand -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-mango rounded-xl flex items-center justify-center transform rotate-3 group-hover:rotate-6 transition-transform shadow-lg shadow-mango/40">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900 hidden sm:block">{{ setting('store_name', 'Chhito Pasal') }}</h1>
            </a>

            <!-- Desktop Nav & Search -->
            <div class="hidden md:flex items-center flex-1 max-w-2xl px-12 gap-8">
                <nav class="flex gap-6 font-bold text-gray-600">
                    <a href="#" class="hover:text-gray-900 transition-colors">Home</a>
                    <a href="#shop" class="hover:text-gray-900 transition-colors">Shop</a>
                    <a href="#about" class="hover:text-gray-900 transition-colors">About Us</a>
                </nav>
                <div class="flex-1 relative group">
                    <input type="text" x-model="searchQuery" placeholder="Search products..." class="w-full bg-gray-100/80 border-transparent rounded-full py-2.5 pl-12 pr-4 focus:bg-white focus:ring-2 focus:ring-mango focus:border-transparent transition-all shadow-inner font-medium placeholder-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-4 top-3 text-gray-400 group-focus-within:text-mango transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-3">
                <button @click="toggleCart()" class="relative bg-gray-900 text-white p-3 rounded-xl hover:bg-gray-800 transition active:scale-95 shadow-lg hidden md:flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span class="font-bold">Cart</span>
                    <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" x-transition class="absolute -top-2 -right-2 bg-wildOrchid text-white text-xs font-black w-6 h-6 rounded-full flex items-center justify-center border-2 border-white shadow-sm"></span>
                </button>


            </div>
        </div>
    </header>

    <!-- Mobile Slide-out Menu -->
    <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-50 md:hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="mobileMenuOpen = false"></div>
        <div x-show="mobileMenuOpen" x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 w-4/5 max-w-sm bg-white shadow-2xl flex flex-col p-6">
            <div class="flex items-center justify-between mb-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-mango rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-900" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
                    </div>
                    <span class="text-xl font-black text-gray-900">Menu</span>
                </a>
                <button @click="mobileMenuOpen = false" class="text-gray-400 hover:text-gray-900"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            
            <div class="relative mb-8">
                <input type="text" x-model="searchQuery" placeholder="Search..." class="w-full bg-gray-100 border-transparent rounded-xl py-3 pl-10 pr-4 focus:border-mango focus:ring-mango font-bold text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>

            <nav class="flex flex-col gap-6 text-lg font-black text-gray-900">
                <a href="#" @click="mobileMenuOpen = false" class="hover:text-wildOrchid transition">Home</a>
                <a href="#shop" @click="mobileMenuOpen = false" class="hover:text-wildOrchid transition">Shop</a>
                <a href="#about" @click="mobileMenuOpen = false" class="hover:text-wildOrchid transition">About Us</a>

            </nav>
        </div>
    </div>

    <!-- 2. Hero Section -->
    <section class="pt-24 sm:pt-32 pb-10 sm:pb-16 px-4 sm:px-6 lg:px-8 max-w-[1600px] mx-auto">
        <div class="bg-gray-900 rounded-2xl sm:rounded-[3rem] overflow-hidden relative shadow-2xl flex flex-col md:flex-row items-center min-h-[280px] sm:min-h-0">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-900/90 to-transparent z-10 hidden md:block"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-gray-900 via-gray-900/80 to-gray-900 z-10 md:hidden"></div>
            
            <!-- Hero Image Background -->
            @if(setting('hero_image'))
                <img src="{{ Storage::url(setting('hero_image')) }}" class="absolute inset-0 w-full h-full object-cover opacity-50 md:opacity-100" alt="Hero">
            @else
                <img src="https://images.unsplash.com/photo-1606813907291-d86efa9b94db?q=80&w=2074&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-50 md:opacity-100" alt="Hero">
            @endif

            <div class="relative z-20 p-6 sm:p-16 md:p-24 md:w-2/3 lg:w-1/2">
                <span class="inline-block py-1.5 px-4 rounded-full bg-mango text-gray-900 font-black text-xs uppercase tracking-widest mb-6">Flash Sale ⚡️</span>
                <h2 class="text-3xl sm:text-5xl md:text-6xl font-black text-white leading-[1.1] mb-4 sm:mb-6">{!! nl2br(e(setting('hero_title', 'Upgrade your lifestyle.'))) !!}</h2>
                <p class="text-gray-300 text-sm sm:text-lg md:text-xl font-medium mb-6 sm:mb-10 max-w-lg">{{ setting('hero_subtitle', 'Discover the best tech, fashion, and home accessories delivered straight to your door. No hassle, just shopping.') }}</p>
                <a href="#shop" class="inline-flex items-center justify-center bg-white text-gray-900 font-black px-6 sm:px-8 py-3 sm:py-4 rounded-full hover:bg-gray-100 active:scale-95 transition shadow-[0_0_40px_rgba(255,255,255,0.3)] group text-sm sm:text-base">
                    {{ setting('hero_cta', 'Shop Collection') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- 3. Category Pills -->
    <section id="shop" class="sticky top-[60px] md:top-[88px] z-30 bg-[#FDFFFC]/90 backdrop-blur-md py-3 md:py-4">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-2 sm:gap-3 overflow-x-auto no-scrollbar pb-2">
                <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-gray-900 text-white shadow-lg' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50'" class="px-4 md:px-6 py-2 md:py-2.5 rounded-full font-bold whitespace-nowrap border transition-all active:scale-95 text-sm md:text-base">
                    All Products
                </button>
                @foreach($categories as $category)
                    <button @click="activeCategory = '{{ $category->slug }}'" :class="activeCategory === '{{ $category->slug }}' ? 'bg-gray-900 text-white shadow-lg' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50'" class="px-4 md:px-6 py-2 md:py-2.5 rounded-full font-bold whitespace-nowrap border transition-all active:scale-95 text-sm md:text-base">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 4. Product Grid -->
    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pb-32">
        
        <!-- Empty State -->
        <div x-show="filteredProducts.length === 0" x-cloak class="text-center py-20">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">No products found</h3>
            <p class="text-gray-500 font-medium">Try adjusting your search or category filter.</p>
            <button @click="searchQuery = ''; activeCategory = 'all'" class="mt-6 text-wildOrchid font-bold hover:underline">Clear all filters</button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-6 lg:gap-8">
            <template x-for="(product, index) in filteredProducts" :key="product.id">
                <article class="group bg-white rounded-2xl md:rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden flex flex-col transition-all duration-300 hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] hover:-translate-y-1 animate-staggered" :style="`animation-delay: ${index * 0.05}s`">
                    
                    <!-- Image Container (Clickable for Quick View & Product Page) -->
                    <div class="aspect-[4/5] bg-gray-100 relative overflow-hidden group/img">
                        <img :src="'{{ asset('storage') }}/' + product.image_path" :alt="product.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy">
                        
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black/0 group-hover/img:bg-black/5 transition-colors duration-300 flex items-center justify-center gap-2">
                            <button @click="openQuickView(product)" class="bg-white/90 backdrop-blur text-gray-900 font-bold px-4 py-2 rounded-full shadow-lg opacity-0 group-hover/img:opacity-100 transform translate-y-4 group-hover/img:translate-y-0 transition-all duration-300 text-sm flex items-center gap-1 hover:bg-mango hover:text-gray-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                Quick View
                            </button>
                            <a :href="'{{ url('product') }}/' + product.slug" class="bg-gray-900 text-white font-bold px-4 py-2 rounded-full shadow-lg opacity-0 group-hover/img:opacity-100 transform translate-y-4 group-hover/img:translate-y-0 transition-all duration-300 text-sm flex items-center gap-1 hover:bg-gray-800 delay-75">
                                View Details
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-3 md:p-5 lg:p-6 flex flex-col flex-1 relative">
                        <!-- Category Tag -->
                        <span class="text-[10px] md:text-xs font-black uppercase tracking-wider text-wildOrchid mb-1 md:mb-2 block" x-text="product.category?.name || 'Uncategorized'"></span>
                        
                        <h3 class="font-black text-sm md:text-lg lg:text-xl text-gray-900 mb-0.5 md:mb-1 leading-tight line-clamp-1" x-text="product.name"></h3>
                        <div class="flex-1"></div>
                        
                        <div class="flex items-center justify-between mt-auto gap-1">
                            <span class="text-base md:text-xl lg:text-2xl font-black text-gray-900">Rs.<span x-text="product.price.toLocaleString()"></span></span>
                            <button @click.prevent="triggerAddToCart(product)" class="bg-gray-900 text-white font-bold text-xs md:text-sm px-3 md:px-5 py-2 md:py-2.5 rounded-full hover:bg-mango hover:text-gray-900 transition-all duration-300 active:scale-90 shadow-sm flex items-center gap-1 md:gap-1.5 group/btn flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 md:h-4 md:w-4 transform group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                <span class="hidden md:inline">Buy</span>
                            </button>
                        </div>
                    </div>
                </article>
            </template>
        </div>
    </main>

    <!-- 5. Footer -->
    <footer id="about" class="bg-gray-900 text-white pt-12 sm:pt-20 pb-8 sm:pb-10 rounded-t-[2rem] sm:rounded-t-[3rem] mt-10">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 sm:gap-12 mb-10 sm:mb-16">
                <div class="md:col-span-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 mb-6">
                        <div class="w-10 h-10 bg-mango rounded-xl flex items-center justify-center shadow-lg shadow-mango/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
                        </div>
                        <h2 class="text-2xl font-black tracking-tight text-white">{{ setting('store_name', 'Chhito Pasal') }}</h2>
                    </a>
                    <p class="text-gray-400 font-medium max-w-sm mb-6 leading-relaxed">Your premium destination for the finest products. Delivering happiness across the country with lightning speed.</p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:bg-mango hover:text-gray-900 transition-colors"><i class="fa fa-facebook"></i> F</a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:bg-mango hover:text-gray-900 transition-colors"><i class="fa fa-instagram"></i> I</a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 hover:bg-mango hover:text-gray-900 transition-colors"><i class="fa fa-twitter"></i> T</a>
                    </div>
                </div>
                <div>
                    <h4 class="font-black text-lg mb-6">Quick Links</h4>
                    <ul class="space-y-4 text-gray-400 font-medium">
                        <li><a href="#" class="hover:text-mango transition-colors">Home</a></li>
                        <li><a href="#shop" class="hover:text-mango transition-colors">Shop</a></li>
                        <li><a href="#" class="hover:text-mango transition-colors">Categories</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-mango transition-colors">Staff Login</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-black text-lg mb-6">Support</h4>
                    <ul class="space-y-4 text-gray-400 font-medium">
                        <li><a href="mailto:{{ setting('contact_email', 'support@chhitopasal.com') }}" class="hover:text-mango transition-colors">{{ setting('contact_email', 'support@chhitopasal.com') }}</a></li>
                        <li><a href="tel:{{ setting('contact_phone', '+977 9800000000') }}" class="hover:text-mango transition-colors">{{ setting('contact_phone', '+977 9800000000') }}</a></li>
                        <li><a href="#" class="hover:text-mango transition-colors cursor-default">{{ setting('contact_address', 'Kathmandu, Nepal') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row items-center justify-between text-gray-500 font-medium text-sm">
                <p>&copy; {{ date('Y') }} {{ setting('store_name', 'Chhito Pasal') }}. All rights reserved.</p>
                <p class="mt-2 md:mt-0">Crafted with ❤️ for premium shopping.</p>
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
                                    <div class="flex items-center gap-1.5 bg-green-50 text-green-700 px-3 py-1.5 rounded-lg border border-green-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        <span x-text="selectedProduct.stock + ' in stock'"></span>
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
                <button @click="processAddToCart(bundleProduct, 1, bundleProduct.price, false)" class="w-full border-2 border-gray-200 rounded-2xl p-4 flex justify-between items-center hover:border-mango hover:bg-mango/5 transition text-left group">
                    <div>
                        <span class="block font-black text-gray-900 text-lg">Single Piece</span>
                        <span class="block text-gray-500 text-sm">Standard price</span>
                    </div>
                    <span class="font-black text-xl text-gray-900 group-hover:text-mango transition">Rs.<span x-text="bundleProduct?.price.toLocaleString()"></span></span>
                </button>
                
                <!-- Bundles -->
                <template x-for="bundle in bundleProduct?.bundles" :key="bundle.qty">
                    <button @click="processAddToCart(bundleProduct, parseInt(bundle.qty), bundle.price / parseInt(bundle.qty), true)" class="w-full border-2 border-mango bg-mango/10 rounded-2xl p-4 flex justify-between items-center hover:bg-mango/20 transition text-left group relative overflow-hidden">
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

    <!-- The Checkout Slide-out Drawer / Cart Sidebar -->
    <!-- Retaining the existing Sidebar/Drawer logic from Phase 1, just refactoring variable usage -->
    
    <!-- Mobile Floating Tray -->
    <div x-show="totalCartQuantity > 0" x-transition.scale.origin.bottom.right class="fixed bottom-6 right-6 z-40 md:hidden cursor-pointer active:scale-90 transition-transform" @click="toggleCart()">
        <div class="relative bg-mango w-16 h-16 rounded-full shadow-2xl flex items-center justify-center border-4 border-[#FDFFFC] animate-bounce">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            <span x-text="totalCartQuantity" class="absolute -top-1 -right-1 bg-wildOrchid text-white text-xs font-black px-2 py-0.5 rounded-full shadow-md border-2 border-white"></span>
        </div>
    </div>

    <!-- The Cart Modal Overlay -->
    <div x-show="cartOpen" x-cloak class="fixed inset-0 z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="cartOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="cartOpen = false"></div>

        <!-- The Drawer (Mobile: Slide Up, Desktop: Slide Left) -->
        <div x-show="cartOpen" 
             x-transition:enter="transform transition ease-out duration-300 sm:duration-400" 
             x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full" 
             x-transition:enter-end="translate-y-0 md:translate-x-0" 
             x-transition:leave="transform transition ease-in duration-300 sm:duration-400" 
             x-transition:leave-start="translate-y-0 md:translate-x-0" 
             x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full" 
             class="fixed bottom-0 md:top-0 right-0 w-full md:w-[450px] h-[85vh] md:h-screen bg-white rounded-t-[2rem] md:rounded-none md:rounded-l-[2rem] shadow-2xl flex flex-col">
            
            <!-- Header -->
            <div class="px-6 py-6 border-b border-gray-100 flex items-center justify-between bg-white/80 backdrop-blur rounded-t-[2rem] md:rounded-none md:rounded-tl-[2rem]">
                <h2 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-mango" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    Your Cart
                </h2>
                <button @click="cartOpen = false" class="text-gray-400 hover:text-gray-900 bg-gray-50 p-2 rounded-full transition-colors active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
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
                    <div class="flex gap-4 mb-4 bg-white p-4 rounded-3xl shadow-sm border border-gray-100 items-center">
                        <img :src="'{{ asset('storage') }}/' + item.image_path" :alt="item.name" class="w-20 h-20 object-cover rounded-2xl bg-gray-50">
                        <div class="flex-1">
                            <h3 class="font-black text-gray-900 text-base leading-tight mb-1" x-text="item.name"></h3>
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
                <div x-show="cart.length > 0" class="mt-8 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                    <h3 class="font-black text-lg text-gray-900 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-wildOrchid" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Delivery Details
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <input type="text" x-model="customer.name" placeholder="Full Name" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-medium py-3">
                        </div>
                        <div>
                            <input type="tel" x-model="customer.phone" placeholder="Phone Number" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-medium py-3">
                        </div>
                        <div>
                            <textarea x-model="customer.address" placeholder="Full Delivery Address" rows="2" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-medium py-3 resize-none"></textarea>
                        </div>
                        
                        <div class="pt-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Delivery Location</label>
                            <select x-model="customer.delivery_location" class="w-full bg-gray-50 border-gray-200 rounded-xl focus:ring-mango focus:border-mango font-bold py-3 text-gray-900 cursor-pointer">
                                <option value="inside">Inside Kathmandu Valley (+ Rs. 50)</option>
                                <option value="outside">Outside Kathmandu Valley (+ Rs. 100)</option>
                            </select>
                        </div>

                        <p x-show="formError" x-text="formError" class="text-red-500 text-sm font-bold bg-red-50 p-3 rounded-xl border border-red-100"></p>
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

                triggerAddToCart(product) {
                    if (product.bundles && product.bundles.length > 0) {
                        this.bundleProduct = product;
                        this.bundleSelectionOpen = true;
                    } else {
                        this.processAddToCart(product, 1, product.price, false);
                    }
                },

                processAddToCart(product, qty, unitPrice, isBundle) {
                    const cartItemId = `${product.id}_${qty}`;
                    const existing = this.cart.find(i => i.cartItemId === cartItemId);
                    
                    if (existing) {
                        existing.quantity += qty;
                    } else {
                        this.cart.push({ ...product, quantity: qty, price: unitPrice, isBundle: isBundle, cartItemId: cartItemId });
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
                    return this.customer.delivery_location === 'inside' ? 50 : 100;
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
                        delivery_charge: this.deliveryCharge,
                        source: 'Web',
                        items: this.cart.map(item => ({
                            id: item.id,
                            quantity: item.quantity
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
