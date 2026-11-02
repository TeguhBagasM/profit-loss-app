<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold tracking-tight">Ubah COA</h1>
    </x-slot>

    @if ($hasTransactions)
        <div class="mb-4 max-w-xl border border-line border-l-2 border-l-loss bg-surface px-4 py-3 text-sm text-ink">
            COA ini sudah punya transaksi. Memindah kategori berarti reklasifikasi: angka laporan
            historis ikut berpindah.
        </div>
    @endif

    <form method="POST" action="{{ route('chart-of-accounts.update', $chartOfAccount) }}"
          class="max-w-xl space-y-5 border border-line bg-surface p-6">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="code" value="Kode" />
            <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                          :value="old('code', $chartOfAccount->code)" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $chartOfAccount->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="category_id" value="Kategori" />
            <select id="category_id" name="category_id" class="input-field">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        @selected((string) old('category_id', $chartOfAccount->category_id) === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-1">
            <x-primary-button>
                <i class="fa-solid fa-floppy-disk w-3.5 text-center" aria-hidden="true"></i>
                Simpan perubahan
            </x-primary-button>
            <a href="{{ route('chart-of-accounts.index') }}" class="text-sm text-muted underline">Batal</a>
        </div>
    </form>
</x-app-layout>
