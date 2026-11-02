<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-md bg-loss px-4 py-2 text-sm font-medium text-white hover:bg-loss/90 focus-visible:ring-2 focus-visible:ring-loss focus-visible:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
