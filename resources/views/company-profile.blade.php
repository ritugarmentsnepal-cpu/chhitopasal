<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Company Profile - {{ setting('store_name', 'ChhitoPasal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');
        
        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: #050505; 
            color: #ffffff;
            overflow-x: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        /* Safe Glassmorphism */
        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        /* Ambient Glowing Backgrounds */
        .glow-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            pointer-events: none;
            background: 
                radial-gradient(circle at 15% 50%, rgba(139, 92, 246, 0.15), transparent 50%),
                radial-gradient(circle at 85% 30%, rgba(6, 182, 212, 0.15), transparent 50%);
        }

        .text-gradient {
            background: linear-gradient(135deg, #c084fc 0%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Smooth Entrances */
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
    </style>
</head>
<body x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 50)">

    <div class="glow-bg"></div>

    <!-- Top Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 p-6 flex justify-between items-center transition-all duration-500 fade-up" :class="loaded ? 'visible' : ''">
        <a href="{{ route('home') }}" class="w-12 h-12 flex items-center justify-center rounded-full glass-panel hover:bg-white/10 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </a>
    </nav>

    <main class="relative z-10 pt-32 pb-24 px-6 max-w-lg mx-auto min-h-screen flex flex-col">
        
        <!-- Header -->
        <header class="mb-12 text-center fade-up" :class="loaded ? 'visible' : ''">
            <div class="inline-flex items-center justify-center w-24 h-24 mb-6 rounded-[2rem] glass-panel p-2">
                @if(setting('store_logo'))
                    <img src="{{ asset('storage/' . setting('store_logo')) }}" class="w-full h-full object-contain">
                @else
                    <span class="text-3xl font-black text-gradient">{{ substr(setting('store_name', 'CP'), 0, 2) }}</span>
                @endif
            </div>
            <h1 class="text-4xl font-extrabold tracking-tight mb-2">{{ setting('store_name', 'ChhitoPasal') }}</h1>
            <p class="text-cyan-400 text-sm font-semibold tracking-wider uppercase">
                {{ setting('contact_address', 'Kathmandu, Nepal') }}
            </p>
        </header>

        <!-- Intro Panel -->
        <section class="glass-panel p-8 mb-6 fade-up delay-100" :class="loaded ? 'visible' : ''">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                About Us
            </h2>
            <p class="text-gray-300 leading-relaxed font-light text-[15px]">
                {!! nl2br(e(setting('company_intro', 'Welcome to our futuristic storefront. We are dedicated to providing you with the best products and a seamless shopping experience.'))) !!}
            </p>
        </section>

        <!-- Contact Links -->
        <div class="grid gap-4 fade-up delay-200" :class="loaded ? 'visible' : ''">
            <a href="tel:{{ setting('order_contact_number', '') }}" class="glass-panel p-6 flex items-center gap-4 hover:bg-white/10 transition-colors active:scale-95 duration-200">
                <div class="w-12 h-12 rounded-full bg-cyan-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <div class="flex-1">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.1em] mb-1">Orders & Sales</div>
                    <div class="text-lg font-semibold text-white">{{ setting('order_contact_number', 'Not Available') }}</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </a>

            <a href="tel:{{ setting('complain_contact_number', '') }}" class="glass-panel p-6 flex items-center gap-4 hover:bg-white/10 transition-colors active:scale-95 duration-200">
                <div class="w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                </div>
                <div class="flex-1">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.1em] mb-1">Support</div>
                    <div class="text-lg font-semibold text-white">{{ setting('complain_contact_number', 'Not Available') }}</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </a>
        </div>

        <div class="mt-auto pt-12 text-center fade-up delay-300" :class="loaded ? 'visible' : ''">
            <p class="text-xs font-semibold tracking-widest text-gray-500 uppercase">
                &copy; {{ date('Y') }} {{ setting('store_name', 'ChhitoPasal') }}
            </p>
        </div>

    </main>
</body>
</html>
