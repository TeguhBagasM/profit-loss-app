<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ProfitLossReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
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
                'transactions' => collect(),
            ]);
        }

        return view('dashboard', [
            'month' => $month,
            'report' => $this->reportService->build($month, $month),
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
