<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class AssetCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:assets.view', only: ['index']),
            new Middleware('permission:assets.create', only: ['create', 'store']),
            new Middleware('permission:assets.edit', only: ['edit', 'update']),
            new Middleware('permission:assets.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('admin.asset-categories.index', [
            'categories' => AssetCategory::withCount('assets')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.asset-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        AssetCategory::create($this->validateData($request));

        return redirect()->route('admin.asset-categories.index')->with('status', 'category-created');
    }

    public function edit(AssetCategory $assetCategory): View
    {
        return view('admin.asset-categories.edit', ['category' => $assetCategory]);
    }

    public function update(Request $request, AssetCategory $assetCategory): RedirectResponse
    {
        $assetCategory->update($this->validateData($request, $assetCategory));

        return redirect()->route('admin.asset-categories.index')->with('status', 'category-updated');
    }

    public function destroy(AssetCategory $assetCategory): RedirectResponse
    {
        $assetCategory->delete();

        return redirect()->route('admin.asset-categories.index')->with('status', 'category-deleted');
    }

    protected function validateData(Request $request, ?AssetCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:asset_categories,code,'.($category?->id)],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
