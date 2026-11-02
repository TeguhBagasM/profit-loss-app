<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Kategori</h1>
                <p class="mt-1 text-sm text-muted">Kelompokkan pemasukan dan pengeluaran.</p>
            </div>

            <a href="{{ route('categories.create') }}"
               class="inline-flex items-center gap-2 rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                <i class="fa-solid fa-plus w-3.5 text-center" aria-hidden="true"></i>
                Tambah kategori
            </a>
        </div>
    </x-slot>

    <x-flash />

    <x-data-table class="mt-2">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Tipe</th>
                <th class="text-right">Jumlah COA</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td class="text-muted">{{ $category->type }}</td>
                    <td class="text-right tabular-nums">{{ $category->chart_of_accounts_count }}</td>
                    <td>
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('categories.edit', $category) }}" class="text-sm text-accent underline">
                                Ubah
                            </a>

                            <x-confirm-delete
                                :action="route('categories.destroy', $category)"
                                title="Hapus kategori ini?"
                                description="Kategori yang masih dipakai COA tidak bisa dihapus. Pindahkan atau hapus COA-nya dulu."
                                confirm-label="Hapus kategori"
                            />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        <x-empty-state
                            icon="fa-solid fa-tags"
                            title="Belum ada kategori"
                            description="Tambahkan kategori pemasukan atau pengeluaran supaya transaksi bisa dikelompokkan."
                            action-label="Tambah kategori"
                            :action-href="route('categories.create')"
                        />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-data-table>
</x-app-layout>
