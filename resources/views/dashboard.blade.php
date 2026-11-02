<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold tracking-tight">Dashboard</h1>
    </x-slot>

    <p class="text-sm text-muted">
        Selamat datang, {{ Auth::user()->name }}. Pilih bagian di samping untuk mulai mencatat.
    </p>

    <ul class="mt-6 space-y-2 text-sm">
        <li>
            <a href="{{ route('transactions.index') }}" class="text-accent underline">Lihat transaksi</a>
        </li>
        <li>
            <a href="{{ route('reports.profit-loss') }}" class="text-accent underline">Buka laporan profit/loss</a>
        </li>
    </ul>
</x-app-layout>
