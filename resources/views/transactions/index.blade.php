<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Transaksi</h1>
                <p class="mt-1 text-sm text-muted">Catatan pemasukan dan pengeluaran.</p>
            </div>

            <a href="{{ route('transactions.create') }}"
               class="inline-flex items-center gap-2 rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                <i class="fa-solid fa-plus w-3.5 text-center" aria-hidden="true"></i>
                Tambah transaksi
            </a>
        </div>
    </x-slot>

    <x-flash />

    <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-end gap-3 border-b border-line pb-4">
        <div>
            <x-input-label for="date_from" value="Dari tanggal" />
            <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="input-field w-auto">
        </div>

        <div>
            <x-input-label for="date_to" value="Sampai tanggal" />
            <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="input-field w-auto">
        </div>

        <div>
            <x-input-label for="chart_of_account_id" value="COA" />
            <select id="chart_of_account_id" name="chart_of_account_id" class="input-field w-auto">
                <option value="">Semua COA</option>
                @foreach ($chartOfAccounts as $coa)
                    <option value="{{ $coa->id }}"
                        @selected((string) ($filters['chart_of_account_id'] ?? '') === (string) $coa->id)>
                        {{ $coa->code }} - {{ $coa->name }}
                    </option>
                @endforeach
            </select>
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

        <div>
            <x-input-label for="search" value="Cari deskripsi" />
            <x-text-input id="search" name="search" type="text" class="mt-1 block w-auto" :value="$filters['search'] ?? ''" />
        </div>

        <button type="submit"
                class="rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
            Terapkan
        </button>
    </form>

    @foreach (['date_from', 'date_to', 'chart_of_account_id', 'category_id', 'search', 'sort', 'direction'] as $field)
        @error($field)
            <p class="mt-3 text-sm text-loss">{{ $message }}</p>
        @enderror
    @endforeach

    <x-data-table class="mt-6">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Deskripsi</th>
                <th>COA</th>
                <th>Kategori</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Credit</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td class="tabular-nums whitespace-nowrap">{{ $transaction->date->format('Y-m-d') }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td>{{ $transaction->chartOfAccount->code }} - {{ $transaction->chartOfAccount->name }}</td>
                    <td class="text-muted">{{ $transaction->chartOfAccount->category->name }}</td>
                    <td class="text-right"><x-money :value="$transaction->debit" /></td>
                    <td class="text-right"><x-money :value="$transaction->credit" /></td>
                    <td>
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('transactions.edit', $transaction) }}" class="text-sm text-accent underline">
                                <i class="fa-solid fa-pen-to-square w-3.5 text-center" aria-hidden="true"></i>
                                Ubah
                            </a>

                            <x-confirm-delete
                                :action="route('transactions.destroy', $transaction)"
                                title="Hapus transaksi ini?"
                                description="Transaksi yang dihapus tidak bisa dikembalikan."
                                confirm-label="Hapus transaksi"
                            />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-empty-state
                            icon="fa-solid fa-receipt"
                            title="Belum ada transaksi yang cocok"
                            description="Ubah filter di atas, atau catat transaksi pertama Anda."
                            action-label="Tambah transaksi"
                            :action-href="route('transactions.create')"
                        />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-data-table>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</x-app-layout>
