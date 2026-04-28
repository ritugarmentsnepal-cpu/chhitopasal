<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
                    {{ __('ERP Control Panel') }}
                </h2>
                <p class="text-sm font-bold text-gray-500 mt-1">Manage all configuration and business rules globally.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#F8FAFC] min-h-screen">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8" style="max-width: 1600px;">
            <!-- Sidebar Navigation -->
            <div class="flex-shrink-0 w-full md:w-auto" style="min-width: 260px; max-width: 260px;">
                <nav class="space-y-2 sticky top-32">
                    <a href="{{ route('settings.index', ['tab' => 'frontend']) }}" 
                       class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ $tab === 'frontend' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-900' }}">
                        <svg class="w-5 h-5 mr-3 {{ $tab === 'frontend' ? 'text-gray-900' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Frontend & Branding
                    </a>
                    
                    <a href="{{ route('settings.index', ['tab' => 'erp']) }}" 
                       class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ $tab === 'erp' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-900' }}">
                        <svg class="w-5 h-5 mr-3 {{ $tab === 'erp' ? 'text-gray-900' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        ERP & Invoicing
                    </a>
                    
                    <a href="{{ route('settings.index', ['tab' => 'integrations']) }}" 
                       class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ $tab === 'integrations' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-900' }}">
                        <svg class="w-5 h-5 mr-3 {{ $tab === 'integrations' ? 'text-gray-900' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        API Integrations
                    </a>
                    
                    <a href="{{ route('settings.index', ['tab' => 'staff']) }}" 
                       class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ $tab === 'staff' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-900' }}">
                        <svg class="w-5 h-5 mr-3 {{ $tab === 'staff' ? 'text-gray-900' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Staff Roles (RBAC)
                    </a>
                    
                    <a href="{{ route('settings.index', ['tab' => 'automation']) }}" 
                       class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ $tab === 'automation' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-900' }}">
                        <svg class="w-5 h-5 mr-3 {{ $tab === 'automation' ? 'text-gray-900' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Automation Rules
                    </a>
                    
                    <div class="border-t border-gray-200 my-3"></div>
                    
                    <a href="{{ route('settings.index', ['tab' => 'danger']) }}" 
                       class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ $tab === 'danger' ? 'bg-red-100 text-red-700 shadow-sm' : 'text-red-500 hover:bg-red-50 hover:text-red-700' }}">
                        <svg class="w-5 h-5 mr-3 {{ $tab === 'danger' ? 'text-red-600' : 'text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Danger Zone
                    </a>
                </nav>
            </div>

            <!-- Content Area -->
            <div class="flex-1 min-w-0">
                @if (session('success'))
                    <div class="mb-6 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-bold">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 bg-red-500 text-white px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-bold">{{ session('error') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-6 bg-red-50 text-red-500 px-6 py-4 rounded-2xl shadow-sm border border-red-100 flex flex-col gap-1">
                        @foreach ($errors->all() as $error)
                            <div class="font-bold flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $error }}
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="transition-opacity duration-300">
                    @include('settings.tabs.' . $tab)
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
