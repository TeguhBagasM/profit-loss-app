<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Services\ProfitLossReportService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_grafik_sama_dengan_total_laporan_untuk_rentang_yang_sama(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('dashboard'))->assertOk();
        $chart = $response->viewData('chart');
        $range = $response->viewData('chartRange');

        // Bulan transaksi terbaru pada seeder adalah 2022-01, jadi jendela grafik
        // adalah 6 bulan terakhir: 2021-08 sampai 2022-01.
        $this->assertSame(['from' => '2021-08', 'to' => '2022-01'], $range);

        $report = app(ProfitLossReportService::class)->build(
            CarbonImmutable::createFromFormat('!Y-m', '2021-08'),
            CarbonImmutable::createFromFormat('!Y-m', '2022-01'),
        );

        $this->assertSame($report->periods, $chart['labels']);

        foreach ($report->periods as $index => $period) {
            $this->assertSame($report->incomeTotalFor($period), $chart['income'][$index]);
            $this->assertSame($report->expenseTotalFor($period), $chart['expense'][$index]);
            $this->assertSame($report->netFor($period), $chart['net'][$index]);
        }

        $this->assertSame('0.00', $chart['income'][0]);
        $this->assertSame('12000000.00', $chart['income'][5]);
        $this->assertSame('25000.00', $chart['expense'][5]);
    }

    public function test_canvas_punya_role_img_dan_aria_label(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('role="img"', false)
            ->assertSee('aria-label="Income dan expense per bulan, 2021-08 sampai 2022-01"', false)
            ->assertSee('Lihat laporan lengkap')
            ->assertSee(route('reports.profit-loss', ['from' => '2021-08', 'to' => '2022-01']));
    }

    public function test_tanpa_data_menampilkan_empty_state_tanpa_canvas(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada transaksi')
            ->assertDontSee('id="dashboard-chart"', false)
            ->assertDontSee('data-chart', false);
    }
}
