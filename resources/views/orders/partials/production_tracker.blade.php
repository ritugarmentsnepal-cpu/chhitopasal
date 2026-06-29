{{-- Production Tracker Component --}}
{{-- Usage: @include('orders.partials.production_tracker', ['order' => $order]) --}}

@if($order->isCustomPrint())
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mt-3" x-data="{ showForm: false }">
  {{-- Production Pipeline Visual --}}
  <div class="flex items-center justify-between mb-4">
    <h4 class="font-black text-sm text-gray-900 flex items-center gap-2">
      <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
      Production Pipeline
    </h4>
    @if($order->production_status !== 'ready_to_ship')
      <button @click="showForm = !showForm" class="text-xs font-bold text-purple-600 hover:text-purple-800 transition">
        <span x-text="showForm ? 'Cancel' : 'Update Status'"></span>
      </button>
    @endif
  </div>

  {{-- Pipeline Steps --}}
  @php
    $steps = [
      'design_received' => ['label' => 'Design Received', 'icon' => '📥', 'color' => 'blue'],
      'design_approved' => ['label' => 'Design Approved', 'icon' => '✅', 'color' => 'green'],
      'in_production' => ['label' => 'In Production', 'icon' => '⚙️', 'color' => 'yellow'],
      'quality_check' => ['label' => 'Quality Check', 'icon' => '🔍', 'color' => 'orange'],
      'ready_to_ship' => ['label' => 'Ready to Ship', 'icon' => '📦', 'color' => 'emerald'],
    ];
    $statuses = array_keys($steps);
    $currentIndex = array_search($order->production_status, $statuses);
    if ($currentIndex === false) $currentIndex = -1;
  @endphp

  <div class="flex items-center gap-1 overflow-x-auto pb-2">
    @foreach($steps as $key => $step)
      @php
        $stepIndex = array_search($key, $statuses);
        $isCompleted = $stepIndex < $currentIndex;
        $isCurrent = $stepIndex === $currentIndex;
        $isPending = $stepIndex > $currentIndex;
      @endphp
      <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
        <div class="flex flex-col items-center min-w-[70px]">
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
            {{ $isCompleted ? 'bg-green-100 text-green-700' : ($isCurrent ? 'bg-purple-100 text-purple-700 ring-2 ring-purple-300' : 'bg-gray-100 text-gray-400') }}">
            {{ $step['icon'] }}
          </div>
          <span class="text-[9px] font-bold mt-1 text-center leading-tight
            {{ $isCurrent ? 'text-purple-700' : ($isCompleted ? 'text-green-600' : 'text-gray-400') }}">
            {{ $step['label'] }}
          </span>
        </div>
        @if(!$loop->last)
          <div class="flex-1 h-0.5 mx-1 rounded {{ $isCompleted ? 'bg-green-300' : 'bg-gray-200' }}"></div>
        @endif
      </div>
    @endforeach
  </div>

  {{-- Print Details Summary --}}
  <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-bold">
    @if($order->print_method)
      <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full uppercase">{{ str_replace('_', ' ', $order->print_method) }}</span>
    @endif
    @if($order->print_positions)
      @foreach($order->print_positions as $pos)
        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full">{{ str_replace('_', ' ', ucfirst($pos)) }}</span>
      @endforeach
    @endif
    @if($order->estimated_delivery_date)
      <span class="px-2 py-0.5 bg-orange-50 text-orange-700 rounded-full">ETA: {{ $order->estimated_delivery_date->format('M j') }}</span>
    @endif
    @if($order->advance_amount > 0)
      <span class="px-2 py-0.5 bg-green-50 text-green-700 rounded-full">Advance: Rs. {{ number_format($order->advance_amount) }}</span>
    @endif
  </div>

  {{-- Size Breakdown --}}
  @php $item = $order->orderItems->first(); @endphp
  @if($item && !empty($item->size_breakdown))
    <div class="mt-2 flex flex-wrap gap-1">
      @foreach($item->size_breakdown as $size => $qty)
        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[10px] font-bold">{{ $size }}: {{ $qty }}</span>
      @endforeach
    </div>
  @endif

  {{-- Design File & Notes --}}
  @if($order->design_files)
    <div class="mt-2 space-y-1">
      @foreach($order->design_files as $position => $path)
        <a href="{{ asset('storage/' . $path) }}" target="_blank" class="text-xs font-bold text-purple-600 hover:text-purple-800 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
          {{ ucfirst(str_replace('_', ' ', $position)) }} Design
        </a>
      @endforeach
    </div>
  @elseif($order->design_file)
    <div class="mt-2">
      <a href="{{ asset('storage/' . $order->design_file) }}" target="_blank" class="text-xs font-bold text-purple-600 hover:text-purple-800 flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
        View Design File
      </a>
    </div>
  @endif

  @if($order->design_notes)
    <p class="mt-1 text-[10px] text-gray-500 italic line-clamp-2">📝 {{ $order->design_notes }}</p>
  @endif

  {{-- Status Update Form --}}
  <div x-show="showForm" x-transition class="mt-4 border-t border-gray-100 pt-4">
    <form action="{{ route('orders.updateProductionStatus', $order) }}" method="POST" class="space-y-3">
      @csrf
      <div>
        <label class="block text-xs font-black text-gray-400 uppercase mb-1">Next Status</label>
        <select name="production_status" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2">
          @php
            $nextStatuses = match($order->production_status) {
              null => ['design_received' => 'Design Received'],
              'design_received' => ['design_approved' => 'Design Approved'],
              'design_approved' => ['in_production' => 'Start Production', 'design_received' => '← Back to Design Received'],
              'in_production' => ['quality_check' => 'Quality Check'],
              'quality_check' => ['ready_to_ship' => 'Ready to Ship', 'in_production' => '← Back to Production'],
              default => [],
            };
          @endphp
          @foreach($nextStatuses as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-black text-gray-400 uppercase mb-1">Notes (Optional)</label>
        <input type="text" name="production_notes" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium py-2" placeholder="e.g. Colors look perfect, proceeding...">
      </div>
      <button type="submit" class="bg-purple-600 text-white font-bold text-sm px-5 py-2 rounded-xl hover:bg-purple-700 transition active:scale-95">
        Update Production
      </button>
    </form>
  </div>

  @if($order->production_notes)
    <div class="mt-2 bg-gray-50 rounded-lg px-3 py-2">
      <p class="text-[10px] font-bold text-gray-500">Production Notes:</p>
      <p class="text-xs text-gray-700">{{ $order->production_notes }}</p>
    </div>
  @endif
</div>
@endif
