@use('App\Support\Money')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Transaksi
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-800 text-sm rounded">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 text-red-800 text-sm rounded">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('transactions.index') }}"
                  class="mb-4 flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="date_from" value="Dari tanggal" />
                    <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"
                           class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                </div>

                <div>
                    <x-input-label for="date_to" value="Sampai tanggal" />
                    <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"
                           class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                </div>

                <div>
                    <x-input-label for="chart_of_account_id" value="COA" />
                    <select id="chart_of_account_id" name="chart_of_account_id"
                            class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                    <select id="category_id" name="category_id"
                            class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                    <x-text-input id="search" name="search" type="text" class="mt-1 block"
                                  :value="$filters['search'] ?? ''" />
                </div>

                <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded">
                    Terapkan
                </button>

                <a href="{{ route('transactions.create') }}"
                   class="px-4 py-2 border border-gray-300 text-sm rounded">
                    Tambah transaksi
                </a>
            </form>

            @foreach (['date_from', 'date_to', 'chart_of_account_id', 'category_id', 'search', 'sort', 'direction'] as $field)
                @error($field)
                    <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endforeach

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left">
                            <th class="px-4 py-2 font-medium">Tanggal</th>
                            <th class="px-4 py-2 font-medium">Deskripsi</th>
                            <th class="px-4 py-2 font-medium">COA</th>
                            <th class="px-4 py-2 font-medium">Kategori</th>
                            <th class="px-4 py-2 font-medium text-right">Debit</th>
                            <th class="px-4 py-2 font-medium text-right">Credit</th>
                            <th class="px-4 py-2 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-2 tabular-nums whitespace-nowrap">
                                    {{ $transaction->date->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-2">{{ $transaction->description }}</td>
                                <td class="px-4 py-2">
                                    {{ $transaction->chartOfAccount->code }} - {{ $transaction->chartOfAccount->name }}
                                </td>
                                <td class="px-4 py-2">{{ $transaction->chartOfAccount->category->name }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ Money::format($transaction->debit) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ Money::format($transaction->credit) }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('transactions.edit', $transaction) }}" class="underline">
                                            Ubah
                                        </a>

                                        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                                              onsubmit="return confirm('Hapus transaksi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada transaksi yang cocok. Ubah filter atau tambah transaksi baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
