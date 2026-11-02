<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kategori
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

            <div class="mb-4">
                <a href="{{ route('categories.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm rounded">
                    Tambah kategori
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left">
                            <th class="px-4 py-2 font-medium">Nama</th>
                            <th class="px-4 py-2 font-medium">Tipe</th>
                            <th class="px-4 py-2 font-medium">Jumlah COA</th>
                            <th class="px-4 py-2 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-2">{{ $category->name }}</td>
                                <td class="px-4 py-2">{{ $category->type }}</td>
                                <td class="px-4 py-2 tabular-nums">{{ $category->chart_of_accounts_count }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('categories.edit', $category) }}" class="underline">
                                            Ubah
                                        </a>

                                        <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                              onsubmit="return confirm('Hapus kategori ini?')">
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
                                    Belum ada kategori. Tambahkan kategori pertama untuk mulai mencatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
