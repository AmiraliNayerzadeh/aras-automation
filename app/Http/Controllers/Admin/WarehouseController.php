<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class WarehouseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:warehouse.view', only: ['index']),
            new Middleware('permission:warehouse.manage', only: ['create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    public function index(): View
    {
        return view('admin.warehouses.index', [
            'warehouses' => Warehouse::withCount('stockBalances')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Warehouse::create($this->validateData($request));

        return redirect()->route('admin.warehouses.index')->with('status', 'warehouse-created');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($this->validateData($request, $warehouse));

        return redirect()->route('admin.warehouses.index')->with('status', 'warehouse-updated');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')->with('status', 'warehouse-deleted');
    }

    protected function validateData(Request $request, ?Warehouse $warehouse = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code,'.($warehouse?->id)],
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
