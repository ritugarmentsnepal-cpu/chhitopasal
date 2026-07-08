<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="font-black text-2xl text-gray-900 leading-tight tracking-tight flex items-center gap-3">
          <span class="text-2xl">🤖</span> {{ __('AI Agent') }}
          @if($aiEnabled)
            <span class="bg-green-100 text-green-700 font-bold text-xs px-3 py-1 rounded-full">ACTIVE</span>
          @else
            <span class="bg-gray-100 text-gray-500 font-bold text-xs px-3 py-1 rounded-full">INACTIVE</span>
          @endif
        </h2>
        <p class="text-sm font-bold text-gray-500 mt-1">Train, monitor, and manage your AI-powered Facebook Messenger agent.</p>
      </div>
    </div>
  </x-slot>

  <div class="py-6 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
    @if(session('success'))
      <div class="bg-green-50 border border-green-200 text-green-700 font-bold px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="bg-red-50 border border-red-200 text-red-700 font-bold px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ session('error') }}
      </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="flex gap-1 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100 mb-6">
      @php
        $tabs = [
          'dashboard' => ['icon' => '📊', 'label' => 'Dashboard'],
          'knowledge' => ['icon' => '📚', 'label' => 'Knowledge Base'],
          'products' => ['icon' => '🏷️', 'label' => 'Product Training'],
          'training' => ['icon' => '💬', 'label' => 'Conversation Training'],
          'settings' => ['icon' => '⚙️', 'label' => 'Settings'],
        ];
      @endphp
      @foreach($tabs as $key => $tabInfo)
        <a href="{{ route('ai-agent.index', ['tab' => $key]) }}"
          class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all {{ $tab === $key ? 'bg-gray-900 text-white shadow-lg' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
          <span>{{ $tabInfo['icon'] }}</span>
          {{ $tabInfo['label'] }}
        </a>
      @endforeach
    </div>

    {{-- ======================= DASHBOARD TAB ======================= --}}
    @if($tab === 'dashboard')
      @include('ai-agent.tabs.dashboard')
    @endif

    {{-- ======================= KNOWLEDGE BASE TAB ======================= --}}
    @if($tab === 'knowledge')
      @include('ai-agent.tabs.knowledge')
    @endif

    {{-- ======================= PRODUCT TRAINING TAB ======================= --}}
    @if($tab === 'products')
      @include('ai-agent.tabs.products')
    @endif

    {{-- ======================= CONVERSATION TRAINING TAB ======================= --}}
    @if($tab === 'training')
      @include('ai-agent.tabs.training')
    @endif

    {{-- ======================= SETTINGS TAB ======================= --}}
    @if($tab === 'settings')
      @include('ai-agent.tabs.settings')
    @endif
  </div>
</x-app-layout>
