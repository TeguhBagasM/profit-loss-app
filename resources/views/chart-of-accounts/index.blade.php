<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Chart of Account
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

            <form method="GET" action="{{ route('chart-of-accounts.index') }}"
                  class="mb-4 flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="search" value="Cari kode atau nama" />
                    <x-text-input id="search" name="search" type="text" class="mt-1 block"
                                  :value="$filters['search'] ?? ''" />
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

                <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded">
                    Terapkan
                </button>

                <a href="{{ route('chart-of-accounts.create') }}"
                   class="px-4 py-2 border border-gray-300 text-sm rounded">
                    Tambah COA
                </a>
            </form>

            @error('search')
                <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('category_id')
                <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left">
                            <th class="px-4 py-2 font-medium">Kode</th>
                            <th class="px-4 py-2 font-medium">Nama</th>
                            <th class="px-4 py-2 font-medium">Kategori</th>
                            <th class="px-4 py-2 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($chartOfAccounts as $chartOfAccount)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-2 tabular-nums">{{ $chartOfAccount->code }}</td>
                                <td class="px-4 py-2">{{ $chartOfAccount->name }}</td>
                                <td class="px-4 py-2">{{ $chartOfAccount->category->name }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('chart-of-accounts.edit', $chartOfAccount) }}" class="underline">
                                            Ubah
                                        </a>

                                        <form method="POST"
                                              action="{{ route('chart-of-accounts.destroy', $chartOfAccount) }}"
                                              onsubmit="return confirm('Hapus COA ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                    Tidak ada COA yang cocok. Ubah kata kunci atau tambah COA baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $chartOfAccounts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
