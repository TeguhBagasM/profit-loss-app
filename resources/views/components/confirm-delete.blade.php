@props([
    'action',
    'label' => 'Hapus',
    'title' => 'Hapus data ini?',
    'description' => 'Tindakan ini tidak bisa dibatalkan.',
    'confirmLabel' => 'Hapus',
    'name' => null,
])

@php
    $modalName = $name ?? 'confirm-delete-'.\Illuminate\Support\Str::random(8);
@endphp

<button type="button" x-data x-on:click="$dispatch('open-modal', '{{ $modalName }}')"
        class="text-sm text-loss underline hover:text-loss/80">
    {{ $label }}
</button>

<x-modal :name="$modalName" max-width="md" focusable>
    <form method="POST" action="{{ $action }}" class="p-6">
        @csrf
        @method('DELETE')

        <h2 class="text-base font-medium text-ink">{{ $title }}</h2>
        <p class="mt-2 text-sm text-muted">{{ $description }}</p>

        <div class="mt-6 flex justify-end gap-2">
            <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>

            <button type="submit"
                    class="inline-flex items-center rounded-md bg-loss px-4 py-2 text-sm font-medium text-white hover:bg-loss/90 focus-visible:ring-2 focus-visible:ring-loss focus-visible:ring-offset-2">
                {{ $confirmLabel }}
            </button>
        </div>
    </form>
</x-modal>
