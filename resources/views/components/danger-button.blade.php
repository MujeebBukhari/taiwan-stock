<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-150 ease-in-out shadow-xs cursor-pointer']) }}>
    {{ $slot }}
</button>
