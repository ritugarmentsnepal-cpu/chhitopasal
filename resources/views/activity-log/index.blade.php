<x-app-layout>
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-8">

  {{-- ── PAGE HEADER ────────────────────────────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h2 class="text-2xl font-black text-gray-900 tracking-tight">Activity Log</h2>
      <p class="text-gray-500 font-medium mt-1">Comprehensive audit trail for your entire system.</p>
    </div>
  </div>

  {{-- ── TAB SWITCHER ────────────────────────────────────────────────────── --}}
  <div>
    <div class="flex gap-2 bg-white p-2 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-gray-100 w-fit">
      <a href="{{ route('activity-log.index', ['tab' => 'admin']) }}"
        class="px-5 py-2.5 rounded-full font-bold text-sm transition-all {{ $tab === 'admin' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 ' }}">
        👤 Admin Activity
      </a>
      <a href="{{ route('activity-log.index', ['tab' => 'system']) }}"
        class="px-5 py-2.5 rounded-full font-bold text-sm transition-all {{ $tab === 'system' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 ' }}">
        🖥️ System Logs
      </a>
      <a href="{{ route('activity-log.index', ['tab' => 'customer']) }}"
        class="px-5 py-2.5 rounded-full font-bold text-sm transition-all {{ $tab === 'customer' ? 'bg-mango text-gray-900 shadow-sm' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 ' }}">
        🛍️ Customer Activity
      </a>
    </div>

    {{-- ╔══════════════════════════════════════════════════╗ --}}
    {{-- ║     TAB 1: ADMIN ACTIVITY         ║ --}}
    {{-- ╚══════════════════════════════════════════════════╝ --}}
    @if($tab === 'admin')
      @include('activity-log.tabs.admin')
    @endif

    {{-- ╔══════════════════════════════════════════════════╗ --}}
    {{-- ║     TAB 2: SYSTEM LOGS           ║ --}}
    {{-- ╚══════════════════════════════════════════════════╝ --}}
    @if($tab === 'system')
      @include('activity-log.tabs.system')
    @endif

    {{-- ╔══════════════════════════════════════════════════╗ --}}
    {{-- ║     TAB 3: CUSTOMER ACTIVITY        ║ --}}
    {{-- ╚══════════════════════════════════════════════════╝ --}}
    @if($tab === 'customer')
      @include('activity-log.tabs.customer')
    @endif

  </div>
</div>

</x-app-layout>
