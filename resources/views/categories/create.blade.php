<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold tracking-tight">Tambah kategori</h1>
    </x-slot>

    <form method="POST" action="{{ route('categories.store') }}"
          class="max-w-xl space-y-5 border border-line bg-surface p-6">
        @csrf

        <div>
            <x-input-label for="name" value="Nama kategori" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="type" value="Tipe" />
            <select id="type" name="type" class="input-field">
                <option value="income" @selected(old('type') === 'income')>Income</option>
                <option value="expense" @selected(old('type') === 'expense')>Expense</option>
            </select>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-1">
            <x-primary-button>Simpan kategori</x-primary-button>
            <a href="{{ route('categories.index') }}" class="text-sm text-muted underline">Batal</a>
        </div>
    </form>
</x-app-layout>
