<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah COA
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('chart-of-accounts.store') }}"
                  class="bg-white p-6 shadow-sm sm:rounded-lg space-y-4">
                @csrf

                <div>
                    <x-input-label for="code" value="Kode" />
                    <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
                                  :value="old('code')" required autofocus />
                    <p class="mt-1 text-sm text-gray-500">Huruf, angka, titik, dan strip. Maksimal 20 karakter.</p>
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
                    <select id="category_id" name="category_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Simpan COA</x-primary-button>
                    <a href="{{ route('chart-of-accounts.index') }}" class="text-sm underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
