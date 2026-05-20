@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-gray-900 dark:focus:border-gray-500 focus:ring focus:ring-gray-900/10 dark:focus:ring-gray-500/20 py-3 font-medium transition-colors dark:placeholder-gray-500']) }}>
