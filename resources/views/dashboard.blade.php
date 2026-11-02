<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Dashboard</h1>
                <p class="mt-1 text-sm text-muted">
                    @if ($month)
                        Ringkasan {{ $month->format('Y-m') }}
                    @else
                        Belum ada data untuk diringkas
                    @endif
                </p>
            </div>

            <a href="{{ route('reports.profit-loss') }}"
               class="inline-flex items-center rounded-md border border-line bg-surface px-4 py-2 text-sm font-medium text-ink hover:bg-paper focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                Buka laporan
            </a>
        </div>
    </x-slot>

    @if ($report === null)
        <x-empty-state
            icon="fa-solid fa-chart-line"
            title="Belum ada transaksi"
            description="Catat transaksi pertama Anda; ringkasan dan grafik bulan terakhir akan muncul di sini."
            action-label="Tambah transaksi"
            :action-href="route('transactions.create')"
        />
    @else
        <dl class="flex flex-wrap gap-x-10 gap-y-4 border-b border-line pb-4">
            <div>
                <dt class="text-sm text-muted">Total income</dt>
                <dd class="mt-1 text-lg font-semibold"><x-money :value="$report->totalIncome" /></dd>
            </div>

            <div>
                <dt class="text-sm text-muted">Total expense</dt>
                <dd class="mt-1 text-lg font-semibold"><x-money :value="$report->totalExpense" /></dd>
            </div>

            <div>
                <dt class="text-sm text-muted">Net income</dt>
                <dd class="mt-1 text-xl font-bold"><x-money :value="$report->net" /></dd>
            </div>
        </dl>

        <section class="mt-8">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <h2 class="text-base font-medium">Income vs expense per bulan</h2>

                <a href="{{ route('reports.profit-loss', ['from' => $chartRange['from'], 'to' => $chartRange['to']]) }}"
                   class="text-sm text-accent underline">
                    Lihat laporan lengkap
                </a>
            </div>

            <div data-chart="{{ json_encode($chart) }}" class="mt-4 h-56 border-t border-line pt-4 sm:h-72">
                <canvas id="dashboard-chart" role="img"
                        aria-label="Income dan expense per bulan, {{ $chart['labels'][0] }} sampai {{ $chart['labels'][count($chart['labels']) - 1] }}"></canvas>
            </div>

            @vite('resources/js/dashboard-chart.js')
        </section>

        <h2 class="mt-8 text-base font-medium">5 transaksi terakhir</h2>

        <x-data-table class="mt-2">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>COA</th>
                    <th>Kategori</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td class="tabular-nums whitespace-nowrap">{{ $transaction->date->format('Y-m-d') }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ $transaction->chartOfAccount->code }} - {{ $transaction->chartOfAccount->name }}</td>
                        <td class="text-muted">{{ $transaction->chartOfAccount->category->name }}</td>
                        <td class="text-right"><x-money :value="$transaction->debit" /></td>
                        <td class="text-right"><x-money :value="$transaction->credit" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-data-table>
    @endif
</x-app-layout>
