<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ProfitLossReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Jumlah bulan pada grafik dashboard.
     */
    private const CHART_MONTHS = 6;

    public function __construct(
        private readonly ProfitLossReportService $reportService,
    ) {
        //
    }

    public function __invoke(): View
    {
        $month = $this->reportService->latestMonth();

        if ($month === null) {
            return view('dashboard', [
                'month' => null,
                'report' => null,
                'chart' => null,
                'chartRange' => null,
                'transactions' => collect(),
            ]);
        }

        // Grafik memakai 6 bulan terakhir yang berakhir pada bulan transaksi terbaru;
        // bulan tanpa transaksi tetap tampil dengan nilai 0.
        $chartFrom = $month->subMonthsNoOverflow(self::CHART_MONTHS - 1)->startOfMonth();
        $chartReport = $this->reportService->build($chartFrom, $month);

        return view('dashboard', [
            'month' => $month,
            'report' => $this->reportService->build($month, $month),
            'chart' => $chartReport->toChartData(),
            'chartRange' => [
                'from' => $chartFrom->format('Y-m'),
                'to' => $month->format('Y-m'),
            ],
            'transactions' => Transaction::query()
                ->with([
                    'chartOfAccount:id,code,name,category_id',
                    'chartOfAccount.category:id,name',
                ])
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
