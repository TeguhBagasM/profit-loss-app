<?php

declare(strict_types=1);

namespace App\Exports;

use App\Support\ExcelSanitizer;
use App\Support\ProfitLossReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Export laporan Profit/Loss.
 *
 * Sumber data satu-satunya adalah DTO ProfitLossReport dari
 * ProfitLossReportService, sehingga angka export identik dengan halaman laporan.
 */
class ProfitLossExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    private const NUMBER_FORMAT = '#,##0.00';

    public function __construct(
        private readonly ProfitLossReport $report,
    ) {
        //
    }

    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        $rows = [];

        $rows[] = array_merge(
            [ExcelSanitizer::clean('Kategori')],
            array_map(ExcelSanitizer::clean(...), $this->report->periods),
        );

        foreach ($this->report->incomeRows as $row) {
            $rows[] = array_merge([ExcelSanitizer::clean($row['name'])], array_values($row['values']));
        }

        $rows[] = array_merge(
            [ExcelSanitizer::clean('Total Income')],
            array_map(fn (string $period): string => $this->report->incomeTotalFor($period), $this->report->periods),
        );

        foreach ($this->report->expenseRows as $row) {
            $rows[] = array_merge([ExcelSanitizer::clean($row['name'])], array_values($row['values']));
        }

        $rows[] = array_merge(
            [ExcelSanitizer::clean('Total Expense')],
            array_map(fn (string $period): string => $this->report->expenseTotalFor($period), $this->report->periods),
        );

        $rows[] = array_merge(
            [ExcelSanitizer::clean('Net Income')],
            array_map(fn (string $period): string => $this->report->netFor($period), $this->report->periods),
        );

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        $formats = [];

        foreach ($this->periodColumnIndexes() as $index) {
            $formats[Coordinate::stringFromColumnIndex($index)] = self::NUMBER_FORMAT;
        }

        return $formats;
    }

    /**
     * @return array<string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $this->lastColumn();

                foreach ($this->boldRows() as $row) {
                    $sheet->getStyle('A'.$row.':'.$lastColumn.$row)->getFont()->setBold(true);
                }
            },
        ];
    }

    /**
     * Baris yang ditebalkan: Total Income, Total Expense, Net Income.
     *
     * @return list<int>
     */
    private function boldRows(): array
    {
        $totalIncomeRow = 2 + count($this->report->incomeRows);
        $totalExpenseRow = $totalIncomeRow + 1 + count($this->report->expenseRows);

        return [$totalIncomeRow, $totalExpenseRow, $totalExpenseRow + 1];
    }

    /**
     * @return list<int>
     */
    private function periodColumnIndexes(): array
    {
        $count = count($this->report->periods);

        return $count === 0 ? [] : range(2, $count + 1);
    }

    private function lastColumn(): string
    {
        return Coordinate::stringFromColumnIndex(count($this->report->periods) + 1);
    }
}
