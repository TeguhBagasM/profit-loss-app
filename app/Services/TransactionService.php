<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionService
{
    public const PER_PAGE = 15;

    /**
     * Kolom yang boleh dipakai untuk sorting.
     */
    private const SORTABLE = ['date', 'description', 'debit', 'credit'];

    /**
     * @param  array{
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     chart_of_account_id?: int|string|null,
     *     category_id?: int|string|null,
     *     search?: string|null,
     *     sort?: string|null,
     *     direction?: string|null
     * }  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Transaction::query()
            ->with([
                'chartOfAccount:id,code,name,category_id',
                'chartOfAccount.category:id,name',
            ]);

        $this->applyFilters($query, $filters);

        $sort = $filters['sort'] ?? null;

        // Nilai di luar whitelist diabaikan dan jatuh ke urutan default.
        if (is_string($sort) && in_array($sort, self::SORTABLE, true)) {
            $direction = ($filters['direction'] ?? null) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sort, $direction)->orderByDesc('id');
        } else {
            $query->orderByDesc('date')->orderByDesc('id');
        }

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /**
     * @param  array{date: string, chart_of_account_id: int|string, description?: string|null, debit: string, credit: string}  $data
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * @param  array{date: string, chart_of_account_id: int|string, description?: string|null, debit: string, credit: string}  $data
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction;
    }

    public function delete(Transaction $transaction): void
    {
        $transaction->delete();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $dateFrom = $filters['date_from'] ?? null;

        if (is_string($dateFrom) && $dateFrom !== '') {
            $query->where('date', '>=', $dateFrom);
        }

        $dateTo = $filters['date_to'] ?? null;

        if (is_string($dateTo) && $dateTo !== '') {
            // Akhir hari, agar transaksi pada tanggal tersebut ikut terhitung.
            $query->where('date', '<=', $dateTo.' 23:59:59');
        }

        $chartOfAccountId = $filters['chart_of_account_id'] ?? null;

        if ($chartOfAccountId !== null && $chartOfAccountId !== '') {
            $query->where('chart_of_account_id', (int) $chartOfAccountId);
        }

        $categoryId = $filters['category_id'] ?? null;

        if ($categoryId !== null && $categoryId !== '') {
            $query->whereHas('chartOfAccount', function (Builder $query) use ($categoryId): void {
                $query->where('category_id', (int) $categoryId);
            });
        }

        $search = $filters['search'] ?? null;

        if (is_string($search) && $search !== '') {
            $this->applySearch($query, $search);
        }
    }

    private function applySearch(Builder $query, string $term): void
    {
        $pattern = '%'.$this->escapeLike($term).'%';

        // '%' dan '_' di-escape agar diperlakukan sebagai karakter biasa.
        // SQL konstan, seluruh nilai masuk lewat binding.
        $query->whereRaw("description LIKE ? ESCAPE '!'", [$pattern]);
    }

    private function escapeLike(string $term): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
    }
}
