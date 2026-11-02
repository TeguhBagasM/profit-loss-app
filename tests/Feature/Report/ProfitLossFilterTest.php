<?php

namespace Tests\Feature\Report;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitLossFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_laporan_butuh_login(): void
    {
        $this->get(route('reports.profit-loss'))
            ->assertRedirect(route('login'));
    }

    public function test_from_dengan_format_salah_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.profit-loss', ['from' => '01-2022', 'to' => '2022-12']))
            ->assertSessionHasErrors('from');
    }

    public function test_to_dengan_bulan_tidak_valid_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.profit-loss', ['from' => '2022-01', 'to' => '2022-13']))
            ->assertSessionHasErrors('to');
    }

    public function test_to_lebih_awal_dari_from_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.profit-loss', ['from' => '2022-05', 'to' => '2022-04']))
            ->assertSessionHasErrors('to');
    }

    public function test_rentang_lebih_dari_24_bulan_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        // 26 bulan (Januari 2020 s.d. Februari 2022).
        $this->get(route('reports.profit-loss', ['from' => '2020-01', 'to' => '2022-02']))
            ->assertSessionHasErrors('to');
    }

    public function test_rentang_tepat_24_bulan_diterima(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.profit-loss', ['from' => '2020-01', 'to' => '2021-12']))
            ->assertOk()
            ->assertSessionHasNoErrors();
    }

    public function test_default_rentang_memakai_tahun_transaksi_terbaru(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('reports.profit-loss'))->assertOk();

        $periods = $response->viewData('report')->periods;

        $this->assertCount(12, $periods);
        $this->assertSame('2022-01', $periods[0]);
        $this->assertSame('2022-12', $periods[11]);
    }

    public function test_default_rentang_tanpa_transaksi_memakai_tahun_berjalan(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('reports.profit-loss'))->assertOk();

        $periods = $response->viewData('report')->periods;

        $this->assertCount(12, $periods);
        $this->assertSame(now()->year.'-01', $periods[0]);
        $this->assertSame(now()->year.'-12', $periods[11]);
    }

    public function test_halaman_menampilkan_nilai_laporan_dari_data_seeder(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.profit-loss', ['from' => '2022-01', 'to' => '2022-01']))
            ->assertOk()
            ->assertSee('Salary')
            ->assertSee('Transport Expense')
            ->assertSee('Net Income')
            ->assertSee('12.000.000,00')
            ->assertSee('25.000,00')
            ->assertSee('11.975.000,00');
    }

    public function test_transaksi_di_luar_filter_tidak_mempengaruhi_total_halaman(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        // Tambah transaksi di bulan lain; filter Januari 2022 tidak boleh ikut menghitungnya.
        Transaction::create([
            'date' => '2022-02-01',
            'chart_of_account_id' => ChartOfAccount::query()->where('code', '401')->value('id'),
            'description' => 'Gaji Februari',
            'debit' => '0.00',
            'credit' => '999999.00',
        ]);

        $response = $this->get(route('reports.profit-loss', ['from' => '2022-01', 'to' => '2022-01']))
            ->assertOk()
            ->assertDontSee('12.999.999,00');

        $this->assertSame('12000000.00', $response->viewData('report')->totalIncome);
    }
}
