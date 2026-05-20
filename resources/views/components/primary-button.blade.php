<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-3 bg-gray-900 dark:bg-white border border-transparent rounded-xl font-bold text-sm text-white dark:text-gray-900 tracking-wide shadow-[0_8px_20px_rgb(17,24,39,0.2)] hover:bg-gray-800 dark:hover:bg-gray-100 active:scale-95 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
