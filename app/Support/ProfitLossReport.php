<?php

declare(strict_types=1);

namespace App\Support;

/**
 * DTO read-only hasil laporan Profit/Loss.
 *
 * Semua nilai uang berupa string desimal skala 2 dan dijumlah dengan BCMath
 * lewat App\Support\Money. View dan export hanya membaca DTO ini.
 */
final class ProfitLossReport
{
    /**
     * @param  list<string>  $periods  Daftar periode 'Y-m' berurutan.
     * @param  list<array{id: int, name: string, values: array<string, string>}>  $incomeRows
     * @param  list<array{id: int, name: string, values: array<string, string>}>  $expenseRows
     */
    public function __construct(
        public readonly array $periods,
        public readonly array $incomeRows,
        public readonly string $totalIncome,
        public readonly array $expenseRows,
        public readonly string $totalExpense,
        public readonly string $net,
    ) {
        //
    }

    public function incomeTotalFor(string $period): string
    {
        return $this->sumForPeriod($this->incomeRows, $period);
    }

    public function expenseTotalFor(string $period): string
    {
        return $this->sumForPeriod($this->expenseRows, $period);
    }

    public function netFor(string $period): string
    {
        return Money::sub($this->incomeTotalFor($period), $this->expenseTotalFor($period));
    }

    /**
     * @param  list<array{id: int, name: string, values: array<string, string>}>  $rows
     */
    private function sumForPeriod(array $rows, string $period): string
    {
        $total = '0.00';

        foreach ($rows as $row) {
            $total = Money::add($total, $row['values'][$period] ?? '0.00');
        }

        return $total;
    }
}
