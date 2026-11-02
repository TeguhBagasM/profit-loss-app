<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\ProfitLossExport;
use App\Http\Requests\ProfitLossFilterRequest;
use App\Services\ProfitLossReportService;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfitLossReportController extends Controller
{
    public function __construct(
        private readonly ProfitLossReportService $reportService,
    ) {
        //
    }

    public function index(ProfitLossFilterRequest $request): View
    {
        $validated = $request->validated();

        $range = $this->reportService->resolveRange($validated['from'] ?? null, $validated['to'] ?? null);
        $report = $this->reportService->build($range['from'], $range['to']);

        return view('reports.profit-loss', [
            'report' => $report,
            'from' => $range['from']->format('Y-m'),
            'to' => $range['to']->format('Y-m'),
        ]);
    }

    public function export(ProfitLossFilterRequest $request): BinaryFileResponse
    {
        $validated = $request->validated();

        $range = $this->reportService->resolveRange($validated['from'] ?? null, $validated['to'] ?? null);
        $report = $this->reportService->build($range['from'], $range['to']);

        $fileName = 'profit-loss_'.$range['from']->format('Y-m').'_'.$range['to']->format('Y-m').'.xlsx';

        return Excel::download(new ProfitLossExport($report), $fileName);
    }
}
