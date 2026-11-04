<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Transaksi</h1>
                <p class="mt-1 text-sm text-muted">Catatan pemasukan dan pengeluaran.</p>
            </div>

            <a href="{{ route('transactions.create') }}"
               class="inline-flex min-h-10 items-center gap-2 rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                <i class="fa-solid fa-plus w-3.5 text-center" aria-hidden="true"></i>
                Tambah transaksi
            </a>
        </div>
    </x-slot>

    <x-flash />

    <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-col items-stretch gap-3 border-b border-line pb-4 sm:flex-row sm:flex-wrap sm:items-end">
        <div class="w-full sm:w-auto">
            <x-input-label for="date_from" value="Dari tanggal" />
            <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="input-field">
        </div>

        <div class="w-full sm:w-auto">
            <x-input-label for="date_to" value="Sampai tanggal" />
            <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="input-field">
        </div>

        <div class="w-full sm:w-auto sm:min-w-56">
            <x-input-label for="chart_of_account_id" value="COA" />
            <select id="chart_of_account_id" name="chart_of_account_id" class="input-field">
                <option value="">Semua COA</option>
                @foreach ($chartOfAccounts as $coa)
                    <option value="{{ $coa->id }}"
                        @selected((string) ($filters['chart_of_account_id'] ?? '') === (string) $coa->id)>
                        {{ $coa->code }} - {{ $coa->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-full sm:w-auto sm:min-w-48">
            <x-input-label for="category_id" value="Kategori" />
            <select id="category_id" name="category_id" class="input-field">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-full sm:w-auto sm:min-w-56">
            <x-input-label for="search" value="Cari deskripsi" />
            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" :value="$filters['search'] ?? ''" />
        </div>

        <button type="submit"
                class="min-h-10 rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
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
                <th class="hidden sm:table-cell">Deskripsi</th>
                <th>COA</th>
                <th class="hidden lg:table-cell">Kategori</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Credit</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td class="tabular-nums whitespace-nowrap">{{ $transaction->date->format('Y-m-d') }}</td>
                    <td class="hidden sm:table-cell max-w-xs truncate">{{ $transaction->description }}</td>
                    <td>{{ $transaction->chartOfAccount->code }} - {{ $transaction->chartOfAccount->name }}</td>
                    <td class="hidden lg:table-cell text-muted">{{ $transaction->chartOfAccount->category->name }}</td>
                    <td class="text-right whitespace-nowrap tabular-nums"><x-money :value="$transaction->debit" /></td>
                    <td class="text-right whitespace-nowrap tabular-nums"><x-money :value="$transaction->credit" /></td>
                    <td>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('transactions.edit', $transaction) }}" class="inline-flex min-h-10 items-center text-sm text-accent underline">
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

    <div class="mt-4 max-w-full overflow-x-auto">
        {{ $transactions->links() }}
    </div>
</x-app-layout>
