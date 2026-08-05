<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization\Department;
use App\Models\Organization\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class UnitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:organization.view', only: ['index']),
            new Middleware('permission:organization.create', only: ['create', 'store']),
            new Middleware('permission:organization.edit', only: ['edit', 'update']),
            new Middleware('permission:organization.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('admin.units.index', [
            'units' => Unit::with('department')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.units.create', [
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Unit::create($this->validateData($request));

        return redirect()->route('admin.units.index')->with('status', 'unit-created');
    }

    public function edit(Unit $unit): View
    {
        return view('admin.units.edit', [
            'unit' => $unit,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $unit->update($this->validateData($request, $unit));

        return redirect()->route('admin.units.index')->with('status', 'unit-updated');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('admin.units.index')->with('status', 'unit-deleted');
    }

    protected function validateData(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
