<?php

namespace Tests\Feature\Report;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class ProfitLossExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_butuh_login(): void
    {
        $this->get(route('reports.profit-loss.export'))
            ->assertRedirect(route('login'));
    }

    public function test_export_mengembalikan_file_xlsx_dengan_nama_dari_filter(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('reports.profit-loss.export', [
            'from' => '2022-01',
            'to' => '2022-01',
        ]));

        $response->assertOk();
        $response->assertDownload('profit-loss_2022-01_2022-01.xlsx');
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
    }

    public function test_header_periode_dinamis(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $sheet = $this->sheet($this->export(['from' => '2022-01', 'to' => '2022-03']));

        $this->assertSame('Kategori', $sheet->getCell('A1')->getValue());
        $this->assertSame('2022-01', $sheet->getCell('B1')->getValue());
        $this->assertSame('2022-02', $sheet->getCell('C1')->getValue());
        $this->assertSame('2022-03', $sheet->getCell('D1')->getValue());
        $this->assertNull($sheet->getCell('E1')->getValue());
    }

    public function test_sel_angka_bertipe_numerik_dengan_format_dua_desimal(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $sheet = $this->sheet($this->export(['from' => '2022-01', 'to' => '2022-01']));
        $row = $this->rowFor($sheet, 'Salary');
        $cell = $sheet->getCell('B'.$row);

        $this->assertSame(DataType::TYPE_NUMERIC, $cell->getDataType());
        $this->assertSame('12,000,000.00', $cell->getFormattedValue());

        $transport = $this->rowFor($sheet, 'Transport Expense');
        $transportCell = $sheet->getCell('B'.$transport);

        $this->assertSame(DataType::TYPE_NUMERIC, $transportCell->getDataType());
        $this->assertSame('25,000.00', $transportCell->getFormattedValue());
    }

    public function test_nama_kategori_berawalan_sama_dengan_diberi_awalan_petik(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::create(['name' => '=SUM(A1:A2)', 'type' => Category::TYPE_INCOME]);
        $this->transaction($category, '2022-01-01', '100.00');

        $sheet = $this->sheet($this->export(['from' => '2022-01', 'to' => '2022-01']));

        $row = $this->rowFor($sheet, "'=SUM(A1:A2)");

        $this->assertSame("'=SUM(A1:A2)", $sheet->getCell('A'.$row)->getValue());
    }

    public function test_total_export_sama_dengan_total_halaman(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $filters = ['from' => '2022-01', 'to' => '2022-01'];

        $page = $this->get(route('reports.profit-loss', $filters))->assertOk();
        $report = $page->viewData('report');

        $sheet = $this->sheet($this->export($filters));

        $this->assertSame(
            $report->totalIncome,
            $this->decimalFromCell($sheet, 'B'.$this->rowFor($sheet, 'Total Income')),
        );
        $this->assertSame(
            $report->totalExpense,
            $this->decimalFromCell($sheet, 'B'.$this->rowFor($sheet, 'Total Expense')),
        );
        $this->assertSame(
            $report->net,
            $this->decimalFromCell($sheet, 'B'.$this->rowFor($sheet, 'Net Income')),
        );

        $this->assertSame('11975000.00', $report->net);
    }

    public function test_baris_total_dan_net_income_ditebalkan(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $sheet = $this->sheet($this->export(['from' => '2022-01', 'to' => '2022-01']));

        foreach (['Total Income', 'Total Expense', 'Net Income'] as $label) {
            $row = $this->rowFor($sheet, $label);

            $this->assertTrue(
                $sheet->getStyle('A'.$row.':B'.$row)->getFont()->getBold(),
                "Baris {$label} seharusnya ditebalkan.",
            );
        }

        $salaryRow = $this->rowFor($sheet, 'Salary');

        $this->assertFalse(
            (bool) $sheet->getStyle('A'.$salaryRow.':B'.$salaryRow)->getFont()->getBold(),
            'Baris kategori biasa tidak boleh ditebalkan.',
        );
    }

    public function test_rentang_tidak_valid_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.profit-loss.export', ['from' => '2022-05', 'to' => '2022-04']))
            ->assertSessionHasErrors('to');

        $this->get(route('reports.profit-loss.export', ['from' => '2022-13', 'to' => '2022-12']))
            ->assertSessionHasErrors('from');

        $this->get(route('reports.profit-loss.export', ['from' => '2020-01', 'to' => '2022-02']))
            ->assertSessionHasErrors('to');
    }

    public function test_halaman_laporan_menampilkan_tombol_export_dengan_filter_aktif(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.profit-loss', ['from' => '2022-01', 'to' => '2022-06']))
            ->assertOk()
            ->assertSee('Export Excel')
            ->assertSee(route('reports.profit-loss.export', ['from' => '2022-01', 'to' => '2022-06']));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function export(array $filters): TestResponse
    {
        return $this->get(route('reports.profit-loss.export', $filters))->assertOk();
    }

    private function sheet(TestResponse $response): Worksheet
    {
        $path = $response->baseResponse->getFile()->getPathname();

        return IOFactory::load($path)->getActiveSheet();
    }

    private function rowFor(Worksheet $sheet, string $label): int
    {
        foreach ($sheet->toArray() as $index => $row) {
            if (($row[0] ?? null) === $label) {
                return $index + 1;
            }
        }

        $this->fail("Baris [{$label}] tidak ditemukan pada file export.");
    }

    private function decimalFromCell(Worksheet $sheet, string $coordinate): string
    {
        return str_replace(',', '', $sheet->getCell($coordinate)->getFormattedValue());
    }

    private function transaction(Category $category, string $date, string $credit): Transaction
    {
        $account = ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ]);

        return Transaction::create([
            'date' => $date,
            'chart_of_account_id' => $account->id,
            'description' => 'uji export',
            'debit' => '0.00',
            'credit' => $credit,
        ]);
    }
}
