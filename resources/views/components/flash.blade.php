@if (session('status'))
    <div x-data="{ show: true }" x-show="show" role="status"
         class="mb-6 flex items-start justify-between gap-4 border border-line border-l-2 border-l-accent bg-surface px-4 py-3">
        <p class="text-sm text-ink">{{ session('status') }}</p>

        <button type="button" x-on:click="show = false" class="text-sm text-muted hover:text-ink" aria-label="Tutup notifikasi">
            Tutup
        </button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" role="alert"
         class="mb-6 flex items-start justify-between gap-4 border border-line border-l-2 border-l-loss bg-surface px-4 py-3">
        <p class="text-sm text-loss">{{ session('error') }}</p>

        <button type="button" x-on:click="show = false" class="text-sm text-muted hover:text-ink" aria-label="Tutup peringatan">
            Tutup
        </button>
    </div>
@endif
