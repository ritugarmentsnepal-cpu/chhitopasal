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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body { font-family: 'Outfit', sans-serif; background-color: #FDFFFC; }
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
                <div class="w-10 h-10 bg-mango rounded-xl flex items-center justify-center transform rotate-3 group-hover:rotate-6 transition-transform shadow-lg shadow-mango/40">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd" /></svg>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900 hidden sm:block">Chhito <span class="text-wildOrchid">Pasal</span></h1>
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

    <!-- Product Display -->
    <main class="pt-28 pb-16 px-4 sm:px-6 lg:px-8 max-w-[1600px] mx-auto min-h-[80vh]">
        <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden flex flex-col md:flex-row border border-gray-100"
             x-data="{ 
                 activeMedia: '{{ asset('storage/' . $product->image_path) }}', 
                 isVideo: false 
             }">
            
            <!-- Left: Media Gallery -->
            <div class="w-full md:w-1/2 p-6 sm:p-10 flex flex-col gap-6">
                <!-- Main Display -->
                <div class="aspect-square bg-gray-50 rounded-[2rem] overflow-hidden flex items-center justify-center relative border border-gray-100">
                    <template x-if="!isVideo">
                        <img :src="activeMedia" class="w-full h-full object-cover" alt="{{ $product->name }}">
                    </template>
                    <template x-if="isVideo">
                        <video :src="activeMedia" controls autoplay muted class="w-full h-full object-contain bg-black"></video>
                    </template>
                </div>
                
                <!-- Thumbnails Grid -->
                <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                    <!-- Thumbnail: Primary Image -->
                    <button @click="activeMedia = '{{ asset('storage/' . $product->image_path) }}'; isVideo = false" 
                            :class="activeMedia === '{{ asset('storage/' . $product->image_path) }}' ? 'ring-4 ring-mango border-transparent' : 'border-gray-200 opacity-70 hover:opacity-100'"
                            class="w-20 h-20 flex-shrink-0 rounded-2xl overflow-hidden border-2 transition-all active:scale-95 bg-gray-50">
                        <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover">
                    </button>
                    
                    <!-- Thumbnails: Additional Images -->
                    @if($product->additional_images)
                        @foreach($product->additional_images as $img)
                            <button @click="activeMedia = '{{ asset('storage/' . $img) }}'; isVideo = false" 
                                    :class="activeMedia === '{{ asset('storage/' . $img) }}' ? 'ring-4 ring-mango border-transparent' : 'border-gray-200 opacity-70 hover:opacity-100'"
                                    class="w-20 h-20 flex-shrink-0 rounded-2xl overflow-hidden border-2 transition-all active:scale-95 bg-gray-50">
                                <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    @endif

                    <!-- Thumbnail: Video -->
                    @if($product->video_path)
                        <button @click="activeMedia = '{{ asset('storage/' . $product->video_path) }}'; isVideo = true" 
                                :class="activeMedia === '{{ asset('storage/' . $product->video_path) }}' ? 'ring-4 ring-mango border-transparent' : 'border-gray-200 opacity-70 hover:opacity-100'"
                                class="w-20 h-20 flex-shrink-0 rounded-2xl overflow-hidden border-2 transition-all active:scale-95 bg-gray-900 flex items-center justify-center group relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="absolute bottom-1 right-1 bg-black/60 text-white text-[10px] font-bold px-1 rounded">VIDEO</span>
                        </button>
                    @endif
                </div>
            </div>
            
            <!-- Right: Details -->
            <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col border-t md:border-t-0 md:border-l border-gray-100 bg-[#FDFFFC]">
                <span class="text-sm font-black uppercase tracking-wider text-wildOrchid mb-4">{{ $product->category->name ?? 'Uncategorized' }}</span>
                <h1 class="text-4xl sm:text-5xl font-black text-gray-900 mb-6 leading-tight">{{ $product->name }}</h1>
                
                <!-- Dominant Price Block -->
                <div class="bg-gray-50 border border-mango/30 rounded-[2rem] p-6 sm:p-8 mb-8 shadow-[0_10px_30px_rgba(255,209,102,0.15)] relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-mango/20 rounded-full blur-3xl"></div>
                    <p class="text-sm text-gray-500 font-bold uppercase tracking-widest mb-1 relative z-10">Our Price</p>
                    <div class="flex items-baseline gap-2 relative z-10">
                        <span class="text-xl md:text-3xl font-bold text-mango">Rs.</span>
                        <span class="text-6xl md:text-[5rem] lg:text-[6rem] font-black text-gray-900 tracking-tighter leading-none">{{ number_format($product->price) }}</span>
                    </div>
                </div>
                
                <!-- Dominant Buy Button -->
                <button @click="triggerAddToCart({{ json_encode($product) }})" class="mb-10 w-full bg-mango text-gray-900 font-black text-2xl py-6 px-8 rounded-3xl hover:bg-[#ffdf8c] active:scale-[0.98] transition-all shadow-[0_20px_40px_rgba(255,209,102,0.4)] flex items-center justify-center gap-3 transform hover:-translate-y-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    Buy Now
                </button>
                
                <div class="mb-8">
                    <h4 class="font-bold text-gray-900 text-lg mb-3">Product Description</h4>
                    <p class="text-gray-600 font-medium leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
                </div>
                
                <div class="flex items-center gap-4 text-sm font-bold text-gray-600 mb-10">
                    <div class="flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-xl border border-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        <span>{{ $product->stock }} in stock</span>
                    </div>
                    <div class="flex items-center gap-2 bg-gray-100 px-4 py-2 rounded-xl border border-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM5.94 12.06a1 1 0 010-1.41l4-4a1 1 0 011.41 0l4 4a1 1 0 01-1.41 1.41L11 9.41V15a1 1 0 11-2 0V9.41L6.65 11.76a1 1 0 01-1.41 0z" clip-rule="evenodd" /></svg>
                        <span>{{ $product->weight_grams }}g per unit</span>
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
                                <option value="inside">Inside Kathmandu Valley (+ Rs. 50)</option>
                                <option value="outside">Outside Kathmandu Valley (+ Rs. 100)</option>
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

                init() {
                    this.$watch('cart', val => localStorage.setItem('cart', JSON.stringify(val)));
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
                                delivery_charge: this.deliveryCharge,
                                source: 'Web',
                                items: this.cart.map(item => ({ id: item.id, quantity: item.quantity }))
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
