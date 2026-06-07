<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-3 bg-gray-900 border border-transparent rounded-xl font-bold text-sm text-white tracking-wide shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 active:scale-95 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
  {{ $slot }}
</button>
