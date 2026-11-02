@use('App\Support\Money')

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Laporan profit/loss</h1>
                <p class="mt-1 text-sm text-muted">Periode {{ $from }} sampai {{ $to }}</p>
            </div>

            <a href="{{ route('reports.profit-loss.export', ['from' => $from, 'to' => $to]) }}"
               class="inline-flex items-center rounded-md border border-line bg-surface px-4 py-2 text-sm font-medium text-ink hover:bg-paper focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                Export Excel
            </a>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('reports.profit-loss') }}"
          class="flex flex-wrap items-end gap-3 border-b border-line pb-4">
        <div>
            <label for="from" class="block text-sm font-medium text-muted">Dari bulan</label>
            <input id="from" name="from" type="month" value="{{ $from }}" class="input-field w-auto">
        </div>

        <div>
            <label for="to" class="block text-sm font-medium text-muted">Sampai bulan</label>
            <input id="to" name="to" type="month" value="{{ $to }}" class="input-field w-auto">
        </div>

        <button type="submit"
                class="rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
            Tampilkan
        </button>
    </form>

    @error('from')
        <p class="mt-3 text-sm text-loss">{{ $message }}</p>
    @enderror
    @error('to')
        <p class="mt-3 text-sm text-loss">{{ $message }}</p>
    @enderror

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr>
                    <th class="sticky left-0 z-10 border-b border-line bg-paper px-3 py-2 text-left text-sm font-medium text-muted">
                        Kategori
                    </th>
                    @foreach ($report->periods as $period)
                        <th class="border-b border-line px-3 py-2 text-right text-sm font-medium whitespace-nowrap text-muted">
                            {{ $period }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($report->incomeRows as $row)
                    <tr>
                        <td class="sticky left-0 z-10 border-b border-line bg-paper px-3 py-2">
                            {{ $row['name'] }}
                        </td>
                        @foreach ($report->periods as $period)
                            <td class="border-b border-line px-3 py-2 text-right">
                                <x-money :value="$row['values'][$period]" />
                            </td>
                        @endforeach
                    </tr>
                @endforeach

                <tr class="font-semibold">
                    <td class="sticky left-0 z-10 border-y border-line bg-paper px-3 py-2">Total Income</td>
                    @foreach ($report->periods as $period)
                        <td class="border-y border-line px-3 py-2 text-right">
                            <x-money :value="$report->incomeTotalFor($period)" />
                        </td>
                    @endforeach
                </tr>

                @foreach ($report->expenseRows as $row)
                    <tr>
                        <td class="sticky left-0 z-10 border-b border-line bg-paper px-3 py-2">
                            {{ $row['name'] }}
                        </td>
                        @foreach ($report->periods as $period)
                            <td class="border-b border-line px-3 py-2 text-right">
                                <x-money :value="$row['values'][$period]" />
                            </td>
                        @endforeach
                    </tr>
                @endforeach

                <tr class="font-semibold">
                    <td class="sticky left-0 z-10 border-y border-line bg-paper px-3 py-2">Total Expense</td>
                    @foreach ($report->periods as $period)
                        <td class="border-y border-line px-3 py-2 text-right">
                            <x-money :value="$report->expenseTotalFor($period)" />
                        </td>
                    @endforeach
                </tr>

                <tr class="font-bold">
                    <td class="sticky left-0 z-10 border-t border-line bg-paper px-3 py-3 text-base">Net Income</td>
                    @foreach ($report->periods as $period)
                        <td class="border-t border-line px-3 py-3 text-right text-base">
                            <x-money :value="$report->netFor($period)" />
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <dl class="mt-8 flex flex-wrap gap-x-10 gap-y-4 border-t border-line pt-4">
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
</x-app-layout>
