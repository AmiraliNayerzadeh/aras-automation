<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ProductCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view', only: ['index']),
            new Middleware('permission:products.create', only: ['create', 'store']),
            new Middleware('permission:products.edit', only: ['edit', 'update']),
            new Middleware('permission:products.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('admin.product-categories.index', [
            'categories' => ProductCategory::with('parent')->withCount('products')->orderBy('title')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.product-categories.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        ProductCategory::create($this->validateData($request));

        return redirect()->route('admin.product-categories.index')->with('status', 'category-created');
    }

    public function edit(ProductCategory $productCategory): View
    {
        return view('admin.product-categories.edit', ['category' => $productCategory] + $this->formOptions($productCategory));
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        $productCategory->update($this->validateData($request, $productCategory));

        return redirect()->route('admin.product-categories.index')->with('status', 'category-updated');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        $productCategory->delete();

        return redirect()->route('admin.product-categories.index')->with('status', 'category-deleted');
    }

    protected function validateData(Request $request, ?ProductCategory $category = null): array
    {
        $parentRules = ['nullable', 'exists:product_categories,id'];

        if ($category) {
            $parentRules[] = 'not_in:'.$category->id;
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'parent_id' => $parentRules,
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    protected function formOptions(?ProductCategory $except = null): array
    {
        return [
            'categories' => ProductCategory::when($except, fn ($q) => $q->whereKeyNot($except->id))->orderBy('title')->get(),
        ];
    }
}
