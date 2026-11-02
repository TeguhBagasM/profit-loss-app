<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TransactionFilterRequest;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Services\ChartOfAccountService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly ChartOfAccountService $chartOfAccountService,
    ) {
        //
    }

    public function index(TransactionFilterRequest $request): View
    {
        $filters = $request->validated();

        return view('transactions.index', [
            'transactions' => $this->transactionService->paginate($filters),
            'filters' => $filters,
            'chartOfAccounts' => $this->chartOfAccountService->chartOfAccountOptions(),
            'categories' => $this->chartOfAccountService->categoryOptions(),
        ]);
    }

    public function create(): View
    {
        return view('transactions.create', [
            'chartOfAccounts' => $this->chartOfAccountService->chartOfAccountOptions(),
        ]);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $this->transactionService->create($request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi ditambahkan.');
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.edit', [
            'transaction' => $transaction,
            'chartOfAccounts' => $this->chartOfAccountService->chartOfAccountOptions(),
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->transactionService->update($transaction, $request->validated());

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->transactionService->delete($transaction);

        return redirect()
            ->route('transactions.index')
            ->with('status', 'Transaksi dihapus.');
    }
}
