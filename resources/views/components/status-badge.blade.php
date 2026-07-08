@props(['status'])
@php
    // PHASE-3.1: one source of truth for order-status colors
    $classes = match ($status) {
        'pending' => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'shipped' => 'bg-indigo-100 text-indigo-700',
        'delivered' => 'bg-emerald-100 text-emerald-700',
        'failed', 'rejected' => 'bg-red-100 text-red-700',
        'return_delivered' => 'bg-orange-100 text-orange-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp
<span {{ $attributes->merge(['class' => "text-xs font-black uppercase px-3 py-1 rounded-full {$classes}"]) }}>{{ str_replace('_', ' ', $status) }}</span>
