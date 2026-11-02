<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Category;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ChartOfAccountService
{
    public const PER_PAGE = 15;

    /**
     * @param  array{search?: string|null, category_id?: int|null}  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = ChartOfAccount::query()->with('category:id,name');

        $search = $filters['search'] ?? null;

        if (is_string($search) && $search !== '') {
            $this->applySearch($query, $search);
        }

        $categoryId = $filters['category_id'] ?? null;

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('category_id', (int) $categoryId);
        }

        return $query
            ->orderBy('code')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Category>
     */
    public function categoryOptions(): Collection
    {
        return Category::query()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * @return Collection<int, ChartOfAccount>
     */
    public function chartOfAccountOptions(): Collection
    {
        return ChartOfAccount::query()->orderBy('code')->get(['id', 'code', 'name']);
    }

    /**
     * @param  array{code: string, name: string, category_id: int|string}  $data
     */
    public function create(array $data): ChartOfAccount
    {
        return ChartOfAccount::create($data);
    }

    /**
     * @param  array{code: string, name: string, category_id: int|string}  $data
     */
    public function update(ChartOfAccount $chartOfAccount, array $data): ChartOfAccount
    {
        $chartOfAccount->update($data);

        return $chartOfAccount;
    }

    public function delete(ChartOfAccount $chartOfAccount): void
    {
        $transactionCount = $chartOfAccount->transactions()->count();

        if ($transactionCount > 0) {
            throw new BusinessRuleException(
                "COA ini masih punya {$transactionCount} transaksi. Hapus atau pindahkan transaksi tersebut dulu."
            );
        }

        $chartOfAccount->delete();
    }

    public function hasTransactions(ChartOfAccount $chartOfAccount): bool
    {
        return $chartOfAccount->transactions()->exists();
    }

    private function applySearch(Builder $query, string $term): void
    {
        $pattern = '%'.$this->escapeLike($term).'%';

        // '%' dan '_' di-escape agar diperlakukan sebagai karakter biasa.
        // SQL konstan, seluruh nilai masuk lewat binding.
        $query->where(function (Builder $query) use ($pattern): void {
            $query->whereRaw("code LIKE ? ESCAPE '!' OR name LIKE ? ESCAPE '!'", [$pattern, $pattern]);
        });
    }

    private function escapeLike(string $term): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
    }
}
