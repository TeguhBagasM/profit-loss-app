<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Chart of account</h1>
                <p class="mt-1 text-sm text-muted">Daftar akun tempat transaksi dibukukan.</p>
            </div>

            <a href="{{ route('chart-of-accounts.create') }}"
               class="inline-flex items-center rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                Tambah COA
            </a>
        </div>
    </x-slot>

    <x-flash />

    <form method="GET" action="{{ route('chart-of-accounts.index') }}" class="flex flex-wrap items-end gap-3 border-b border-line pb-4">
        <div>
            <x-input-label for="search" value="Cari kode atau nama" />
            <x-text-input id="search" name="search" type="text" class="mt-1 block w-auto" :value="$filters['search'] ?? ''" />
        </div>

        <div>
            <x-input-label for="category_id" value="Kategori" />
            <select id="category_id" name="category_id" class="input-field w-auto">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit"
                class="rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
            Terapkan
        </button>
    </form>

    @error('search')
        <p class="mt-3 text-sm text-loss">{{ $message }}</p>
    @enderror
    @error('category_id')
        <p class="mt-3 text-sm text-loss">{{ $message }}</p>
    @enderror

    <x-data-table class="mt-6">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($chartOfAccounts as $chartOfAccount)
                <tr>
                    <td class="tabular-nums">{{ $chartOfAccount->code }}</td>
                    <td>{{ $chartOfAccount->name }}</td>
                    <td class="text-muted">{{ $chartOfAccount->category->name }}</td>
                    <td>
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('chart-of-accounts.edit', $chartOfAccount) }}" class="text-sm text-accent underline">
                                Ubah
                            </a>

                            <x-confirm-delete
                                :action="route('chart-of-accounts.destroy', $chartOfAccount)"
                                title="Hapus COA ini?"
                                description="COA yang sudah punya transaksi tidak bisa dihapus. Hapus atau pindahkan transaksinya dulu."
                                confirm-label="Hapus COA"
                            />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <x-empty-state
                            title="Tidak ada COA yang cocok"
                            description="Ubah kata kunci pencarian, atau tambahkan COA baru untuk kategori ini."
                            action-label="Tambah COA"
                            :action-href="route('chart-of-accounts.create')"
                        />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-data-table>

    <div class="mt-4">
        {{ $chartOfAccounts->links() }}
    </div>
</x-app-layout>
