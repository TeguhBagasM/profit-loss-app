<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-md border border-line bg-surface px-4 py-2 text-sm font-medium text-ink hover:bg-paper focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
