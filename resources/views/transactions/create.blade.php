<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah transaksi
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('transactions.store') }}"
                  class="bg-white p-6 shadow-sm sm:rounded-lg space-y-4">
                @csrf

                <div>
                    <x-input-label for="date" value="Tanggal" />
                    <input id="date" name="date" type="date" value="{{ old('date') }}" required autofocus
                           class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <x-input-error :messages="$errors->get('date')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="chart_of_account_id" value="COA" />
                    <select id="chart_of_account_id" name="chart_of_account_id" required
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Pilih COA</option>
                        @foreach ($chartOfAccounts as $coa)
                            <option value="{{ $coa->id }}"
                                @selected((string) old('chart_of_account_id') === (string) $coa->id)>
                                {{ $coa->code }} - {{ $coa->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('chart_of_account_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Deskripsi" />
                    <x-text-input id="description" name="description" type="text" class="mt-1 block w-full"
                                  :value="old('description')" />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="debit" value="Debit (pengeluaran)" />
                        <input id="debit" name="debit" type="number" step="0.01" min="0"
                               value="{{ old('debit', '0') }}"
                               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <x-input-error :messages="$errors->get('debit')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="credit" value="Credit (pemasukan)" />
                        <input id="credit" name="credit" type="number" step="0.01" min="0"
                               value="{{ old('credit', '0') }}"
                               class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <x-input-error :messages="$errors->get('credit')" class="mt-2" />
                    </div>
                </div>

                <p class="text-sm text-gray-500">
                    Isi salah satu saja: debit untuk pengeluaran, credit untuk pemasukan.
                </p>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan transaksi</x-primary-button>
                    <a href="{{ route('transactions.index') }}" class="text-sm underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
