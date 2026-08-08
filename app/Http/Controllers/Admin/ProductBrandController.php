<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products\ProductBrand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ProductBrandController extends Controller implements HasMiddleware
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
        return view('admin.product-brands.index', [
            'brands' => ProductBrand::withCount('products')->orderBy('title')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.product-brands.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ProductBrand::create($this->validateData($request));

        return redirect()->route('admin.product-brands.index')->with('status', 'brand-created');
    }

    public function edit(ProductBrand $productBrand): View
    {
        return view('admin.product-brands.edit', ['brand' => $productBrand]);
    }

    public function update(Request $request, ProductBrand $productBrand): RedirectResponse
    {
        $productBrand->update($this->validateData($request));

        return redirect()->route('admin.product-brands.index')->with('status', 'brand-updated');
    }

    public function destroy(ProductBrand $productBrand): RedirectResponse
    {
        $productBrand->delete();

        return redirect()->route('admin.product-brands.index')->with('status', 'brand-deleted');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'en_title' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
