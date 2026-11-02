<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryService
{
    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection
    {
        return Category::query()
            ->withCount('chartOfAccounts')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array{name: string, type: string}  $data
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * @param  array{name: string, type: string}  $data
     */
    public function update(Category $category, array $data): Category
    {
        // Mengubah type akan membalik angka laporan historis.
        if ($data['type'] !== $category->type && $category->transactions()->exists()) {
            throw new BusinessRuleException(
                'Tipe kategori tidak bisa diubah karena kategori ini sudah punya transaksi. '
                .'Buat kategori baru dan pindahkan transaksinya lebih dulu.'
            );
        }

        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        $coaCount = $category->chartOfAccounts()->count();

        if ($coaCount > 0) {
            throw new BusinessRuleException(
                "Kategori ini masih dipakai {$coaCount} COA. Pindahkan atau hapus COA tersebut dulu."
            );
        }

        $category->delete();
    }
}
