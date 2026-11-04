<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold tracking-tight">Tambah COA</h1>
    </x-slot>

    <form method="POST" action="{{ route('chart-of-accounts.store') }}"
          class="w-full max-w-xl space-y-5 border border-line bg-surface p-4 sm:p-6">
        @csrf

        <div>
            <x-input-label for="code" value="Kode" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                          :value="old('code')" required autofocus />
            <p class="mt-1 text-sm text-muted">Huruf, angka, titik, dan strip. Maksimal 20 karakter.</p>
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name')" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="category_id" value="Kategori" />
            <select id="category_id" name="category_id" class="input-field">
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
        </div>

        <div class="flex flex-col items-stretch gap-3 pt-1 sm:flex-row sm:items-center">
            <x-primary-button>
                <i class="fa-solid fa-floppy-disk w-3.5 text-center" aria-hidden="true"></i>
                Simpan COA
            </x-primary-button>
            <a href="{{ route('chart-of-accounts.index') }}" class="text-sm text-muted underline">Batal</a>
        </div>
    </form>
</x-app-layout>
