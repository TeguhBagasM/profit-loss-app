<?php

namespace Tests\Feature\Database;

use App\Services\ProfitLossReportService;
use App\Support\ProfitLossReport;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoReportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoReportSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_tidak_ikut_database_seeder(): void
    {
        $this->seed(DatabaseSeeder::class);

        // DatabaseSeeder hanya memuat 3 transaksi sumber dari soal.
        $this->assertDatabaseCount('transactions', 3);
    }

    public function test_demo_seeder_menghasilkan_net_income_contoh(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoReportSeeder::class);

        $this->assertDatabaseCount('transactions', 15);

        $report = $this->report();

        $this->assertSame([
            '16650000.00',
            '13950000.00',
            '10600000.00',
        ], [
            $report->netFor('2022-01'),
            $report->netFor('2022-02'),
            $report->netFor('2022-03'),
        ]);

        $this->assertSame('12000000.00', $this->row($report->incomeRows, 'Salary')['values']['2022-01']);
        $this->assertSame('5500000.00', $this->row($report->incomeRows, 'Other Income')['values']['2022-01']);
        $this->assertSame('6000000.00', $this->row($report->incomeRows, 'Other Income')['values']['2022-02']);
        $this->assertSame('3500000.00', $this->row($report->incomeRows, 'Other Income')['values']['2022-03']);

        $this->assertSame('500000.00', $this->row($report->expenseRows, 'Family Expense')['values']['2022-01']);
        $this->assertSame('4500000.00', $this->row($report->expenseRows, 'Family Expense')['values']['2022-03']);
        $this->assertSame('200000.00', $this->row($report->expenseRows, 'Transport Expense')['values']['2022-01']);
        $this->assertSame('225000.00', $this->row($report->expenseRows, 'Transport Expense')['values']['2022-03']);
        $this->assertSame('150000.00', $this->row($report->expenseRows, 'Meal Expense')['values']['2022-01']);
        $this->assertSame('300000.00', $this->row($report->expenseRows, 'Meal Expense')['values']['2022-02']);
    }

    public function test_demo_seeder_idempoten(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoReportSeeder::class);
        $this->seed(DemoReportSeeder::class);

        $this->assertDatabaseCount('transactions', 15);
    }

    private function report(): ProfitLossReport
    {
        return $this->app->make(ProfitLossReportService::class)->build(
            CarbonImmutable::createFromFormat('!Y-m', '2022-01'),
            CarbonImmutable::createFromFormat('!Y-m', '2022-03'),
        );
    }

    /**
     * @param  array<int, array{id: int, name: string, values: array<string, string>}>  $rows
     * @return array{id: int, name: string, values: array<string, string>}
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
