@props([
    'label',
    'value',
    'color' => 'text-gray-900',
    'sub' => null,
])
{{-- PHASE-3.1: shared stat card — use on any dashboard/stats bar --}}
<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-100 p-4 shadow-sm']) }}>
    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">{{ $label }}</div>
    <div class="text-2xl font-black {{ $color }} mt-1">{{ $value }}</div>
    @if($sub)
        <div class="text-[10px] font-bold text-gray-400">{{ $sub }}</div>
    @endif
</div>
