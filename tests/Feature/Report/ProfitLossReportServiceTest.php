<?php

namespace Tests\Feature\Report;

use App\Exceptions\BusinessRuleException;
use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Services\ProfitLossReportService;
use App\Support\ProfitLossReport;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitLossReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_dari_data_seeder_januari_2022(): void
    {
        $this->seed();

        $report = $this->build('2022-01', '2022-01');

        $this->assertSame(['2022-01'], $report->periods);

        $salary = $this->row($report->incomeRows, 'Salary');
        $this->assertSame('12000000.00', $salary['values']['2022-01']);

        $transport = $this->row($report->expenseRows, 'Transport Expense');
        $this->assertSame('25000.00', $transport['values']['2022-01']);

        $this->assertSame('12000000.00', $report->totalIncome);
        $this->assertSame('25000.00', $report->totalExpense);
        $this->assertSame('11975000.00', $report->net);

        $this->assertSame('12000000.00', $report->incomeTotalFor('2022-01'));
        $this->assertSame('25000.00', $report->expenseTotalFor('2022-01'));
        $this->assertSame('11975000.00', $report->netFor('2022-01'));
    }

    public function test_kategori_dan_bulan_tanpa_transaksi_tetap_muncul_dengan_nilai_nol(): void
    {
        $this->seed();

        $report = $this->build('2022-02', '2022-03');

        $this->assertSame(['2022-02', '2022-03'], $report->periods);

        // Semua kategori tetap ada walau tidak punya transaksi pada rentang ini.
        $this->assertCount(2, $report->incomeRows);
        $this->assertCount(3, $report->expenseRows);

        foreach ([...$report->incomeRows, ...$report->expenseRows] as $row) {
            $this->assertSame('0.00', $row['values']['2022-02']);
            $this->assertSame('0.00', $row['values']['2022-03']);
        }

        $this->assertSame('0.00', $report->totalIncome);
        $this->assertSame('0.00', $report->totalExpense);
        $this->assertSame('0.00', $report->net);

        $this->assertSame('0.00', $report->netFor('2022-02'));
        $this->assertSame('0.00', $report->netFor('2022-03'));
    }

    public function test_rumus_income_adalah_credit_dikurangi_debit(): void
    {
        $category = Category::create(['name' => 'Uji Income', 'type' => Category::TYPE_INCOME]);
        $account = ChartOfAccount::create([
            'code' => '901',
            'name' => 'Uji COA Income',
            'category_id' => $category->id,
        ]);

        // Debit pada kategori income harus MENGURANGI nilai income.
        $this->transaction('2022-01-05', $account, debit: '250.00', credit: '1000.00');

        $report = $this->build('2022-01', '2022-01');

        $this->assertSame('750.00', $this->row($report->incomeRows, 'Uji Income')['values']['2022-01']);
        $this->assertSame('750.00', $report->totalIncome);
        $this->assertSame('0.00', $report->totalExpense);
        $this->assertSame('750.00', $report->net);
    }

    public function test_rumus_expense_adalah_debit_dikurangi_credit(): void
    {
        $category = Category::create(['name' => 'Uji Expense', 'type' => Category::TYPE_EXPENSE]);
        $account = ChartOfAccount::create([
            'code' => '902',
            'name' => 'Uji COA Expense',
            'category_id' => $category->id,
        ]);

        // Credit pada kategori expense harus MENGURANGI nilai expense.
        $this->transaction('2022-01-05', $account, debit: '400.00', credit: '100.00');

        $report = $this->build('2022-01', '2022-01');

        $this->assertSame('300.00', $this->row($report->expenseRows, 'Uji Expense')['values']['2022-01']);
        $this->assertSame('0.00', $report->totalIncome);
        $this->assertSame('300.00', $report->totalExpense);
        $this->assertSame('-300.00', $report->net);
    }

    public function test_rentang_bulan_inklusif_dan_transaksi_di_luar_rentang_diabaikan(): void
    {
        $category = Category::create(['name' => 'Transport Expense', 'type' => Category::TYPE_EXPENSE]);
        $account = ChartOfAccount::create([
            'code' => '602',
            'name' => 'Bensin',
            'category_id' => $category->id,
        ]);

        $this->transaction('2021-12-31', $account, debit: '1.00');
        $this->transaction('2022-01-01', $account, debit: '10.00');
        $this->transaction('2022-02-28', $account, debit: '20.00');
        $this->transaction('2022-03-01', $account, debit: '40.00');

        $report = $this->build('2022-01', '2022-02');

        $values = $this->row($report->expenseRows, 'Transport Expense')['values'];

        $this->assertSame('10.00', $values['2022-01']);
        $this->assertSame('20.00', $values['2022-02']);
        $this->assertSame('30.00', $report->totalExpense);
    }

    public function test_build_menolak_rentang_terbalik(): void
    {
        $this->expectException(BusinessRuleException::class);

        $this->build('2022-03', '2022-01');
    }

    private function build(string $from, string $to): ProfitLossReport
    {
        return $this->app->make(ProfitLossReportService::class)->build(
            CarbonImmutable::createFromFormat('!Y-m', $from),
            CarbonImmutable::createFromFormat('!Y-m', $to),
        );
    }

    private function transaction(string $date, ChartOfAccount $account, string $debit = '0.00', string $credit = '0.00'): Transaction
    {
        return Transaction::create([
            'date' => $date,
            'chart_of_account_id' => $account->id,
            'description' => 'uji',
            'debit' => $debit,
            'credit' => $credit,
        ]);
    }

    /**
     * @param  array<int, array{name: string, values: array<string, string>}>  $rows
     * @return array{name: string, values: array<string, string>}
     */
    private function row(array $rows, string $name): array
    {
        foreach ($rows as $row) {
            if ($row['name'] === $name) {
                return $row;
            }
        }

        $this->fail("Baris kategori [{$name}] tidak ditemukan pada laporan.");
    }
}
