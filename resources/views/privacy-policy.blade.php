<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Privacy Policy - {{ setting('store_name', 'ChhitoPasal') }}</title>
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
            <h1 class="text-4xl font-extrabold tracking-tight mb-2">Privacy Policy</h1>
            <p class="text-cyan-400 text-sm font-semibold tracking-wider uppercase">
                {{ setting('store_name', 'ChhitoPasal') }}
            </p>
        </header>

        <!-- Content Panel -->
        <section class="glass-panel p-8 mb-6 fade-up delay-100" :class="loaded ? 'visible' : ''">
            <div class="space-y-6 text-gray-300 leading-relaxed font-light text-[15px]">
                <div>
                    <h3 class="text-white font-bold mb-2 text-lg">1. Introduction</h3>
                    <p>Welcome to {{ setting('store_name', 'ChhitoPasal') }}. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you about how we look after your personal data and tell you about your privacy rights.</p>
                </div>
                
                <div>
                    <h3 class="text-white font-bold mb-2 text-lg">2. Data We Collect</h3>
                    <p>We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows: Identity Data (name), Contact Data (delivery address, email address, telephone numbers), and Transaction Data (details about payments and products you have purchased).</p>
                </div>
                
                <div>
                    <h3 class="text-white font-bold mb-2 text-lg">3. How We Use Your Data</h3>
                    <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances: Where we need to perform the contract we are about to enter into or have entered into with you (e.g. delivering an order). Where it is necessary for our legitimate interests and your interests and fundamental rights do not override those interests.</p>
                </div>
                
                <div>
                    <h3 class="text-white font-bold mb-2 text-lg">4. Data Security</h3>
                    <p>We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorised way, altered or disclosed. Your data is protected by industry standard encryption protocols.</p>
                </div>
                
                <div>
                    <h3 class="text-white font-bold mb-2 text-lg">5. Contact Us</h3>
                    <p>If you have any questions about this privacy policy or our privacy practices, please contact us at: <br>
                    <strong>Phone:</strong> {{ setting('order_contact_number', 'Not Available') }}<br>
                    <strong>Address:</strong> {{ setting('contact_address', 'Kathmandu, Nepal') }}</p>
                </div>
            </div>
        </section>

        <div class="mt-auto pt-12 text-center fade-up delay-300" :class="loaded ? 'visible' : ''">
            <p class="text-xs font-semibold tracking-widest text-gray-500 uppercase">
                &copy; {{ date('Y') }} {{ setting('store_name', 'ChhitoPasal') }}
            </p>
        </div>

    </main>
</body>
</html>
