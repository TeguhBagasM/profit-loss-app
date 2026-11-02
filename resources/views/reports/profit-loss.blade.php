@use('App\Support\Money')

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporan Profit/Loss
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('reports.profit-loss') }}" class="mb-4 flex flex-wrap items-end gap-3">
                <div>
                    <label for="from" class="block text-sm text-gray-700">Dari bulan</label>
                    <input id="from" name="from" type="month" value="{{ $from }}"
                           class="mt-1 rounded border-gray-300 text-sm">
                </div>
                <div>
                    <label for="to" class="block text-sm text-gray-700">Sampai bulan</label>
                    <input id="to" name="to" type="month" value="{{ $to }}"
                           class="mt-1 rounded border-gray-300 text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded">
                    Tampilkan
                </button>

                <a href="{{ route('reports.profit-loss.export', ['from' => $from, 'to' => $to]) }}"
                   class="px-4 py-2 border border-gray-300 text-sm rounded">
                    Export Excel
                </a>
            </form>

            @error('from')
                <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('to')
                <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left">
                            <th class="px-4 py-2 font-medium">Kategori</th>
                            @foreach ($report->periods as $period)
                                <th class="px-4 py-2 font-medium text-right whitespace-nowrap">{{ $period }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report->incomeRows as $row)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-2">{{ $row['name'] }}</td>
                                @foreach ($report->periods as $period)
                                    <td class="px-4 py-2 text-right tabular-nums">
                                        {{ Money::format($row['values'][$period]) }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        <tr class="border-y border-gray-300 font-semibold">
                            <td class="px-4 py-2">Total Income</td>
                            @foreach ($report->periods as $period)
                                <td class="px-4 py-2 text-right tabular-nums">
                                    {{ Money::format($report->incomeTotalFor($period)) }}
                                </td>
                            @endforeach
                        </tr>

                        @foreach ($report->expenseRows as $row)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-2">{{ $row['name'] }}</td>
                                @foreach ($report->periods as $period)
                                    <td class="px-4 py-2 text-right tabular-nums">
                                        {{ Money::format($row['values'][$period]) }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        <tr class="border-y border-gray-300 font-semibold">
                            <td class="px-4 py-2">Total Expense</td>
                            @foreach ($report->periods as $period)
                                <td class="px-4 py-2 text-right tabular-nums">
                                    {{ Money::format($report->expenseTotalFor($period)) }}
                                </td>
                            @endforeach
                        </tr>

                        <tr class="font-bold">
                            <td class="px-4 py-2">Net Income</td>
                            @foreach ($report->periods as $period)
                                <td class="px-4 py-2 text-right tabular-nums">
                                    {{ Money::format($report->netFor($period)) }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <dl class="mt-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-gray-500">Total Income</dt>
                    <dd class="tabular-nums">{{ Money::format($report->totalIncome) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Total Expense</dt>
                    <dd class="tabular-nums">{{ Money::format($report->totalExpense) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Net Income</dt>
                    <dd class="tabular-nums font-semibold">{{ Money::format($report->net) }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>
