@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-black text-gray-400 uppercase tracking-wider mb-2']) }}>
  {{ $value ?? $slot }}
</label>
