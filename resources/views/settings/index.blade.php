<x-app-layout>
  <x-slot name="header">
    <div>
      <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight">
        {{ __('ERP Control Panel') }}
      </h2>
      <p class="text-sm font-bold text-gray-500 mt-1">Manage all configuration and business rules globally.</p>
    </div>
  </x-slot>

  @php
    $settingsTabs = [
      'frontend' => ['label' => 'Frontend & Branding', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
      'erp' => ['label' => 'ERP & Invoicing', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
      'integrations' => ['label' => 'Integrations', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
      'staff' => ['label' => 'Staff & RBAC', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
      'automation' => ['label' => 'Automation', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
      'danger' => ['label' => 'Danger Zone', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ];
  @endphp

  <div class="py-6">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Horizontal Tab Bar -->
      <div class="mb-6 bg-white p-2 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-gray-100 flex overflow-x-auto no-scrollbar gap-1">
        @foreach($settingsTabs as $key => $t)
          @if($key === 'danger')
            <div class="w-px h-8 bg-gray-200 my-auto mx-1 hidden sm:block"></div>
          @endif
          <a href="{{ route('settings.index', ['tab' => $key]) }}" class="flex items-center gap-1.5 px-4 py-2.5 rounded-full font-bold text-sm whitespace-nowrap transition-all {{ $key === 'danger' ? ($tab === $key ? 'bg-red-100 text-red-700 shadow-sm' : 'text-red-500 hover:bg-red-50 hover:text-red-700') : ($tab === $key ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 ') }}">
            <svg class="w-4 h-4 shrink-0 {{ $key === 'danger' ? ($tab === $key ? 'text-red-600' : 'text-red-400') : ($tab === $key ? 'text-gray-900' : 'text-gray-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"></path>
            </svg>
            <span class="hidden sm:inline">{{ $t['label'] }}</span>
          </a>
        @endforeach
      </div>

      <!-- Flash Messages -->
      @if (session('success'))
        <div class="mb-6 bg-green-50 text-green-700 border border-green-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
          <div class="bg-green-100 p-1.5 rounded-full"><svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
          <span class="font-bold">{{ session('success') }}</span>
        </div>
      @endif
      @if (session('error'))
        <div class="mb-6 bg-red-50 text-red-700 border border-red-100 rounded-2xl px-6 py-4 shadow-sm flex items-center gap-3 animate-[fadeInUp_0.3s_ease-out]">
          <div class="bg-red-100 p-1.5 rounded-full"><svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
          <span class="font-bold">{{ session('error') }}</span>
        </div>
      @endif
      @if($errors->any())
        <div class="mb-6 bg-red-50 text-red-700 border border-red-100 rounded-2xl px-6 py-4 shadow-sm">
          @foreach ($errors->all() as $error)
            <div class="font-bold flex items-center gap-2 py-1">
              <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              {{ $error }}
            </div>
          @endforeach
        </div>
      @endif

      <!-- Tab Content -->
      <div class="transition-opacity duration-300">
        @include('settings.tabs.' . $tab)
      </div>
    </div>
  </div>
</x-app-layout>
