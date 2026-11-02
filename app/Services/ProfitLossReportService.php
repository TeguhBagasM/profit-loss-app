<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Category;
use App\Models\Transaction;
use App\Support\Money;
use App\Support\ProfitLossReport;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProfitLossReportService
{
    public const MAX_MONTHS = 24;

    /**
     * Bangun laporan untuk rentang bulan inklusif.
     */
    public function build(CarbonImmutable $from, CarbonImmutable $to): ProfitLossReport
    {
        $from = $from->startOfMonth();
        $to = $to->startOfMonth();

        $months = $this->monthCount($from, $to);

        if ($months < 1) {
            throw new BusinessRuleException('Bulan akhir tidak boleh lebih awal dari bulan awal.');
        }

        if ($months > self::MAX_MONTHS) {
            throw new BusinessRuleException('Rentang laporan maksimal '.self::MAX_MONTHS.' bulan.');
        }

        $periods = $this->periods($from, $to);
        $sums = $this->aggregate($from, $to);

        $incomeRows = [];
        $expenseRows = [];

        // Baris disusun dari SEMUA kategori, bukan dari hasil query, sehingga
        // kategori/bulan tanpa transaksi tetap tampil dengan nilai 0.00.
        $categories = Category::query()
            ->orderBy('id')
            ->get(['id', 'name', 'type']);

        foreach ($categories as $category) {
            $isIncome = $category->type === Category::TYPE_INCOME;
            $values = [];

            foreach ($periods as $period) {
                $debit = $sums[$category->id][$period]['debit'] ?? '0.00';
                $credit = $sums[$category->id][$period]['credit'] ?? '0.00';

                $values[$period] = $isIncome
                    ? Money::sub($credit, $debit)
                    : Money::sub($debit, $credit);
            }

            $row = ['id' => $category->id, 'name' => $category->name, 'values' => $values];

            if ($isIncome) {
                $incomeRows[] = $row;
            } else {
                $expenseRows[] = $row;
            }
        }

        $totalIncome = $this->sumRows($incomeRows);
        $totalExpense = $this->sumRows($expenseRows);

        return new ProfitLossReport(
            periods: $periods,
            incomeRows: $incomeRows,
            totalIncome: $totalIncome,
            expenseRows: $expenseRows,
            totalExpense: $totalExpense,
            net: Money::sub($totalIncome, $totalExpense),
        );
    }

    /**
     * Tentukan rentang bulan dari filter (format 'Y-m') dengan default bagian 6 spec.
     *
     * @return array{from: CarbonImmutable, to: CarbonImmutable}
     */
    public function resolveRange(?string $from, ?string $to): array
    {
        $fromMonth = $this->parseMonth($from);
        $toMonth = $this->parseMonth($to);

        if ($fromMonth === null && $toMonth === null) {
            $year = $this->latestTransactionYear() ?? (int) now()->year;

            return [
                'from' => $this->monthStart($year, 1),
                'to' => $this->monthStart($year, 12),
            ];
        }

        if ($fromMonth === null) {
            return ['from' => $this->monthStart($toMonth->year, 1), 'to' => $toMonth];
        }

        if ($toMonth === null) {
            return ['from' => $fromMonth, 'to' => $this->monthStart($fromMonth->year, 12)];
        }

        return ['from' => $fromMonth, 'to' => $toMonth];
    }

    private function parseMonth(?string $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return CarbonImmutable::createFromFormat('!Y-m', $value);
    }

    private function monthStart(int $year, int $month): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', sprintf('%04d-%02d-01', $year, $month));
    }

    private function latestTransactionYear(): ?int
    {
        $latest = Transaction::query()->max('date');

        if ($latest === null) {
            return null;
        }

        return (int) CarbonImmutable::parse((string) $latest)->year;
    }

    /**
     * @return list<string>
     */
    private function periods(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $periods = [];

        for ($cursor = $from; $cursor <= $to; $cursor = $cursor->addMonthNoOverflow()) {
            $periods[] = $cursor->format('Y-m');
        }

        return $periods;
    }

    private function monthCount(CarbonImmutable $from, CarbonImmutable $to): int
    {
        return (($to->year - $from->year) * 12 + ($to->month - $from->month)) + 1;
    }

    /**
     * Agregasi per kategori per bulan dengan binding; tanpa whereYear agar index tanggal terpakai.
     *
     * @return array<int, array<string, array{debit: string, credit: string}>>
     */
    private function aggregate(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $periodExpression = $this->periodExpression();

        $rows = DB::table('transactions')
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'transactions.chart_of_account_id')
            ->join('categories', 'categories.id', '=', 'chart_of_accounts.category_id')
            // Batas bawah berupa tanggal; batas atas akhir hari agar transaksi pada
            // hari terakhir bulan (tersimpan sebagai 'Y-m-d 00:00:00') tidak terlewat.
            ->where('transactions.date', '>=', $from->startOfMonth()->toDateString())
            ->where('transactions.date', '<=', $to->endOfMonth()->toDateTimeString())
            ->groupBy('categories.id')
            ->groupBy(DB::raw($periodExpression))
            ->select([
                'categories.id as category_id',
                DB::raw($periodExpression.' as period'),
                DB::raw('SUM(transactions.debit) as total_debit'),
                DB::raw('SUM(transactions.credit) as total_credit'),
            ])
            ->get();

        $sums = [];

        foreach ($rows as $row) {
            $sums[(int) $row->category_id][(string) $row->period] = [
                'debit' => $this->decimal($row->total_debit),
                'credit' => $this->decimal($row->total_credit),
            ];
        }

        return $sums;
    }

    /**
     * Ekspresi periode konstan per driver; tidak pernah menyertakan input user.
     */
    private function periodExpression(): string
    {
        return match (DB::getDriverName()) {
            'mysql', 'mariadb' => "DATE_FORMAT(transactions.date, '%Y-%m')",
            'pgsql' => "to_char(transactions.date, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', transactions.date)",
            default => throw new RuntimeException('Driver database tidak didukung untuk laporan.'),
        };
    }

    /**
     * @param  list<array{id: int, name: string, values: array<string, string>}>  $rows
     */
    private function sumRows(array $rows): string
    {
        $total = '0.00';

        foreach ($rows as $row) {
            foreach ($row['values'] as $value) {
                $total = Money::add($total, $value);
            }
        }

        return $total;
    }

    /**
     * Normalisasi nilai agregat dari DB menjadi string desimal skala 2.
     *
     * MySQL/PostgreSQL mengembalikan SUM(decimal) sebagai string. SQLite
     * mengembalikan angka native (int, atau float bila ada pecahan); untuk
     * kasus itu konversi dilakukan SEKALI di batas driver ke string 2 desimal,
     * dan seluruh aritmetika setelahnya memakai BCMath.
     */
    private function decimal(mixed $value): string
    {
        $normalized = match (true) {
            $value === null => '0',
            is_int($value) => (string) $value,
            is_string($value) => $value,
            is_float($value) => sprintf('%.2F', $value),
            default => throw new RuntimeException('Tipe nilai agregat tidak dikenal.'),
        };

        return Money::add($normalized, '0');
    }
}
