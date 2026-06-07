@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-gray-200 bg-gray-50  shadow-sm focus:border-gray-900 focus:ring focus:ring-gray-900/10 py-3 font-medium transition-colors ']) }}>
