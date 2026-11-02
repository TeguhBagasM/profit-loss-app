<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {
        //
    }

    public function index(): View
    {
        return view('categories.index', [
            'categories' => $this->categoryService->all(),
        ]);
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $this->categoryService->update($category, $request->validated());
        } catch (BusinessRuleException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $this->categoryService->delete($category);
        } catch (BusinessRuleException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('categories.index')
            ->with('status', 'Kategori dihapus.');
    }
}
