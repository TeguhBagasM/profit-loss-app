<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ubah kategori
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('categories.update', $category) }}"
                  class="bg-white p-6 shadow-sm sm:rounded-lg space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" value="Nama kategori" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                  :value="old('name', $category->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="type" value="Tipe" />
                    <select id="type" name="type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="income" @selected(old('type', $category->type) === 'income')>Income</option>
                        <option value="expense" @selected(old('type', $category->type) === 'expense')>Expense</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    <p class="mt-2 text-sm text-gray-500">
                        Tipe tidak bisa diubah jika kategori sudah punya transaksi.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan perubahan</x-primary-button>
                    <a href="{{ route('categories.index') }}" class="text-sm underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
