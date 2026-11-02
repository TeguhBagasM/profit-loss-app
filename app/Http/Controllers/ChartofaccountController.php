<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Requests\ChartOfAccountFilterRequest;
use App\Http\Requests\ChartOfAccountRequest;
use App\Models\ChartOfAccount;
use App\Services\ChartOfAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChartOfAccountController extends Controller
{
    public function __construct(
        private readonly ChartOfAccountService $chartOfAccountService,
    ) {
        //
    }

    public function index(ChartOfAccountFilterRequest $request): View
    {
        $filters = $request->validated();

        return view('chart-of-accounts.index', [
            'chartOfAccounts' => $this->chartOfAccountService->paginate($filters),
            'categories' => $this->chartOfAccountService->categoryOptions(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('chart-of-accounts.create', [
            'categories' => $this->chartOfAccountService->categoryOptions(),
        ]);
    }

    public function store(ChartOfAccountRequest $request): RedirectResponse
    {
        $this->chartOfAccountService->create($request->validated());

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('status', 'COA ditambahkan.');
    }

    public function edit(ChartOfAccount $chartOfAccount): View
    {
        return view('chart-of-accounts.edit', [
            'chartOfAccount' => $chartOfAccount,
            'categories' => $this->chartOfAccountService->categoryOptions(),
            'hasTransactions' => $this->chartOfAccountService->hasTransactions($chartOfAccount),
        ]);
    }

    public function update(ChartOfAccountRequest $request, ChartOfAccount $chartOfAccount): RedirectResponse
    {
        $this->chartOfAccountService->update($chartOfAccount, $request->validated());

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('status', 'COA diperbarui.');
    }

    public function destroy(ChartOfAccount $chartOfAccount): RedirectResponse
    {
        try {
            $this->chartOfAccountService->delete($chartOfAccount);
        } catch (BusinessRuleException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('status', 'COA dihapus.');
    }
}
