<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
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
        body { font-family: 'Outfit', sans-serif; background: linear-gradient(180deg, #f8fafc 0%, #FDFFFC 100%); }
        @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp 0.6s ease forwards; }
        .thumb-active { outline: 2px solid #FFD166; outline-offset: 2px; opacity: 1 !important; }
        .thumb-inactive { opacity: 0.5; }
        .thumb-inactive:hover { opacity: 0.85; }
        @keyframes pricePulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.02)} }
        .price-block:hover { animation: pricePulse 1.5s ease infinite; }
        .buy-btn { background: linear-gradient(135deg, #1a1a2e 0%, #2d2b55 100%); transition: all 0.3s ease; }
        .buy-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(26,26,46,0.3); }
        .buy-btn:active { transform: scale(0.98); }
        @media (min-width: 768px) {
            .pdp-layout { flex-direction: row !important; }
            .pdp-gallery { width: 55% !important; }
            .pdp-details { width: 45% !important; }
        }
    </style>
</head>
<body class="antialiased text-gray-900 overflow-x-hidden selection:bg-wildOrchid selection:text-white"
      x-data="shopData()">

    <!-- Header (Search, Menu, Cart) -->
    <header class="fixed top-0 left-0 right-0 z-40 transition-all duration-300 bg-white/80 backdrop-blur-xl shadow-sm py-3">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            
            <button @click="mobileMenuOpen = true" class="md:hidden p-2 -ml-2 text-gray-900 active:scale-95 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
            </button>

            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                @if(setting('store_logo'))
                    <img src="{{ asset('storage/' . setting('store_logo')) }}" alt="{{ setting('store_name', 'Chhito Pasal') }}" class="h-16 w-auto object-contain transform group-hover:scale-105 transition-transform">
                @else
                    <div class="w-10 h-10 bg-mango rounded-xl flex items-center justify-center transform rotate-3 group-hover:rotate-6 transition-transform shadow-lg shadow-mango/40">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-gray-900 hidden sm:block">{{ setting('store_name', 'Chhito Pasal') }}</h1>
                @endif
            </a>

            <div class="hidden md:flex items-center flex-1 max-w-2xl px-12 gap-8">
                <nav class="flex gap-6 font-bold text-gray-600">
                    <a href="{{ route('home') }}" class="hover:text-gray-900 transition-colors">Home</a>
                    <a href="{{ url('/#shop') }}" class="hover:text-gray-900 transition-colors">Shop</a>
                </nav>
            </div>

            <div class="flex items-center gap-3">
                <button @click="toggleCart()" class="relative bg-gray-900 text-white p-3 rounded-xl hover:bg-gray-800 transition active:scale-95 shadow-lg hidden md:flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <span class="font-bold">Cart</span>
                    <span x-show="totalCartQuantity > 0" x-text="totalCartQuantity" x-transition class="absolute -top-2 -right-2 bg-wildOrchid text-white text-xs font-black w-6 h-6 rounded-full flex items-center justify-center border-2 border-white shadow-sm"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <nav style="padding-top: 90px;" class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div style="display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#94a3b8;">
            <a href="{{ route('home') }}" style="color:#64748b; text-decoration:none;">Home</a>
            <span>›</span>
            <a href="{{ url('/#shop') }}" style="color:#64748b; text-decoration:none;">Shop</a>
            <span>›</span>
            <span style="color:#1a1a2e;">{{ $product->name }}</span>
        </div>
    </nav>

    <main class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 pb-20 min-h-[70vh]">
        <div class="pdp-layout fade-up" style="display:flex; flex-direction:column; gap:0; border-radius:24px; overflow:hidden; background:#fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 10px 40px rgba(0,0,0,0.06);"
             x-data="{ activeMedia: '{{ asset('storage/' . $product->image_path) }}', isVideo: false }">

            <!-- Gallery Side -->
            <div class="pdp-gallery" style="width:100%; padding:20px; background:#f8fafc;">
                <!-- Main Image -->
                <div style="aspect-ratio:1/1; border-radius:16px; overflow:hidden; background:#f1f5f9; position:relative;">
                    <template x-if="!isVideo">
                        <img :src="activeMedia" alt="{{ $product->name }}" style="width:100%; height:100%; object-fit:cover; transition: opacity 0.3s ease;">
                    </template>
                    <template x-if="isVideo">
                        <video :src="activeMedia" controls autoplay muted style="width:100%; height:100%; object-fit:contain; background:#000;"></video>
                    </template>
                </div>

                <!-- Thumbnails -->
                <div class="no-scrollbar" style="display:flex; gap:10px; margin-top:14px; overflow-x:auto; padding-bottom:4px;">
                    <button @click="activeMedia = '{{ asset('storage/' . $product->image_path) }}'; isVideo = false"
                            :class="activeMedia === '{{ asset('storage/' . $product->image_path) }}' ? 'thumb-active' : 'thumb-inactive'"
                            style="width:64px; height:64px; flex-shrink:0; border-radius:12px; overflow:hidden; border:2px solid #e2e8f0; cursor:pointer; transition:all 0.2s;">
                        <img src="{{ asset('storage/' . $product->image_path) }}" style="width:100%; height:100%; object-fit:cover;">
                    </button>
                    @if($product->additional_images)
                        @foreach($product->additional_images as $img)
                        <button @click="activeMedia = '{{ asset('storage/' . $img) }}'; isVideo = false"
                                :class="activeMedia === '{{ asset('storage/' . $img) }}' ? 'thumb-active' : 'thumb-inactive'"
                                style="width:64px; height:64px; flex-shrink:0; border-radius:12px; overflow:hidden; border:2px solid #e2e8f0; cursor:pointer; transition:all 0.2s;">
                            <img src="{{ asset('storage/' . $img) }}" style="width:100%; height:100%; object-fit:cover;">
                        </button>
                        @endforeach
                    @endif
                    @if($product->video_path)
                    <button @click="activeMedia = '{{ asset('storage/' . $product->video_path) }}'; isVideo = true"
                            :class="activeMedia === '{{ asset('storage/' . $product->video_path) }}' ? 'thumb-active' : 'thumb-inactive'"
                            style="width:64px; height:64px; flex-shrink:0; border-radius:12px; overflow:hidden; border:2px solid #e2e8f0; cursor:pointer; background:#1a1a2e; display:flex; align-items:center; justify-content:center; transition:all 0.2s;">
                        <svg style="width:28px; height:28px; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Details Side -->
            <div class="pdp-details" style="width:100%; padding:28px 24px 32px 24px; display:flex; flex-direction:column;">
                <!-- Category badge -->
                <span style="display:inline-block; width:fit-content; padding:4px 12px; border-radius:8px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; background:#f0f9ff; color:#0284c7; margin-bottom:14px;">{{ $product->category->name ?? 'Uncategorized' }}</span>

                <!-- Product name -->
                <h1 style="font-size:clamp(22px,4vw,36px); font-weight:900; color:#0f172a; line-height:1.15; margin-bottom:20px;">{{ $product->name }}</h1>

                <!-- Price block -->
                <div class="price-block" style="background:linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border:1px solid rgba(255,209,102,0.3); border-radius:16px; padding:20px 24px; margin-bottom:20px; position:relative; overflow:hidden;">
                    <div style="position:absolute; right:-20px; top:-20px; width:80px; height:80px; background:rgba(255,209,102,0.25); border-radius:50%; filter:blur(20px);"></div>
                    <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#92400e; margin-bottom:4px; position:relative; z-index:1;">Our Price</p>
                    <div style="display:flex; align-items:baseline; gap:6px; position:relative; z-index:1;">
                        <span style="font-size:18px; font-weight:700; color:#d97706;">Rs.</span>
                        <span style="font-size:clamp(36px,5vw,48px); font-weight:900; color:#0f172a; letter-spacing:-0.03em; line-height:1;">{{ number_format($product->price) }}</span>
                    </div>
                </div>

                <!-- Stock + Weight badges -->
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px;">
                    @if($product->in_stock)
                    <div style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:10px; font-size:12px; font-weight:700; background:rgba(236,253,245,0.9); color:#059669; border:1px solid rgba(16,185,129,0.2);">
                        <span style="width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px rgba(16,185,129,0.5); display:inline-block;"></span>
                        In Stock
                    </div>
                    @else
                    <div style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:10px; font-size:12px; font-weight:700; background:rgba(254,242,242,0.9); color:#dc2626; border:1px solid rgba(239,68,68,0.2);">
                        <span style="width:7px; height:7px; border-radius:50%; background:#ef4444; display:inline-block;"></span>
                        Out of Stock
                    </div>
                    @endif
                    <div style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:10px; font-size:12px; font-weight:700; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;">
                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l3 9a5.002 5.002 0 01-6.001 0M18 7l-3 9m-6-9l6-2m0 0V3" /></svg>
                        {{ $product->weight_grams }}g
                    </div>
                </div>

                <!-- Buy button -->
                <button @click="triggerAddToCart({{ json_encode($product) }})" class="buy-btn" style="width:100%; padding:16px 24px; border-radius:16px; border:none; color:#fff; font-size:17px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:24px;">
                    <svg style="width:22px; height:22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    Buy Now
                </button>

                <!-- Description -->
                <div style="margin-bottom:20px;">
                    <h4 style="font-size:14px; font-weight:800; color:#0f172a; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.05em;">Description</h4>
                    <p style="font-size:14px; font-weight:500; color:#64748b; line-height:1.7; white-space:pre-line;">{{ $product->description }}</p>
                </div>

                <!-- Trust badges -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:auto; padding-top:20px; border-top:1px solid #f1f5f9;">
                    <div style="display:flex; align-items:center; gap:8px; padding:10px; border-radius:12px; background:#f8fafc;">
                        <svg style="width:20px; height:20px; color:#6366f1; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        <span style="font-size:11px; font-weight:700; color:#475569;">Secure Checkout</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; padding:10px; border-radius:12px; background:#f8fafc;">
                        <svg style="width:20px; height:20px; color:#f59e0b; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        <span style="font-size:11px; font-weight:700; color:#475569;">Fast Delivery</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; padding:10px; border-radius:12px; background:#f8fafc;">
                        <svg style="width:20px; height:20px; color:#10b981; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        <span style="font-size:11px; font-weight:700; color:#475569;">Cash on Delivery</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; padding:10px; border-radius:12px; background:#f8fafc;">
                        <svg style="width:20px; height:20px; color:#ec4899; flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                        <span style="font-size:11px; font-weight:700; color:#475569;">Quality Assured</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-20 pb-10 mt-10">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 font-medium text-sm">
            <p>&copy; {{ date('Y') }} {{ setting('store_name', 'Chhito Pasal') }}. All rights reserved.</p>
        </div>
    </footer>

    <!-- Mobile Floating Cart -->
    <div x-show="totalCartQuantity > 0" x-transition.scale.origin.bottom.right class="fixed bottom-6 right-6 z-40 md:hidden cursor-pointer active:scale-90 transition-transform" @click="toggleCart()">
        <div class="relative bg-mango w-16 h-16 rounded-full shadow-2xl flex items-center justify-center border-4 border-[#FDFFFC] animate-bounce">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            <span x-text="totalCartQuantity" class="absolute -top-1 -right-1 bg-wildOrchid text-white text-xs font-black px-2 py-0.5 rounded-full shadow-md border-2 border-white"></span>
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
            <button @click="confirmVariantAddToCart()" class="w-full bg-gray-900 text-white font-black py-4 rounded-2xl hover:bg-gray-800 active:scale-95 transition-all shadow-xl shadow-gray-900/20 flex items-center justify-center gap-3 mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                Add to Cart
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
                },

                needsVariants(product) {
                    const cat = product.category;
                    if (!cat) return false;
                    return (cat.has_color_variants && cat.color_options && cat.color_options.length > 0) ||
                           (cat.has_size_variants && cat.size_options && cat.size_options.length > 0);
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
                    if (this.cart.length > 0) this.cartOpen = !this.cartOpen;
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
