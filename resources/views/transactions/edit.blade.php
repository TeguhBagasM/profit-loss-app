<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold tracking-tight">Ubah transaksi</h1>
    </x-slot>

    <form method="POST" action="{{ route('transactions.update', $transaction) }}"
          class="w-full max-w-xl space-y-5 border border-line bg-surface p-4 sm:p-6">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="date" value="Tanggal" />
            <input id="date" name="date" type="date"
                   value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required autofocus class="input-field">
            <x-input-error :messages="$errors->get('date')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="chart_of_account_id" value="COA" />
            <select id="chart_of_account_id" name="chart_of_account_id" required class="input-field">
                @foreach ($chartOfAccounts as $coa)
                    <option value="{{ $coa->id }}"
                        @selected((string) old('chart_of_account_id', $transaction->chart_of_account_id) === (string) $coa->id)>
                        {{ $coa->code }} - {{ $coa->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('chart_of_account_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi" />
            <x-text-input id="description" name="description" type="text" class="mt-1 block w-full"
                          :value="old('description', $transaction->description)" />
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <x-input-label for="debit" value="Debit (pengeluaran)" />
                <x-money-input name="debit" id="debit" :value="old('debit', $transaction->debit)" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('debit')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="credit" value="Credit (pemasukan)" />
                <x-money-input name="credit" id="credit" :value="old('credit', $transaction->credit)" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('credit')" class="mt-2" />
            </div>
        </div>

        <p class="text-sm text-muted">
            Isi salah satu saja: debit untuk pengeluaran, credit untuk pemasukan.
        </p>

        <div class="flex flex-col items-stretch gap-3 pt-1 sm:flex-row sm:items-center">
            <x-primary-button>
                <i class="fa-solid fa-floppy-disk w-3.5 text-center" aria-hidden="true"></i>
                Simpan perubahan
            </x-primary-button>
            <a href="{{ route('transactions.index') }}" class="text-sm text-muted underline">Batal</a>
        </div>
    </form>
</x-app-layout>
