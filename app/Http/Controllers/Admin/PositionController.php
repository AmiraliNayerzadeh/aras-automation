<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization\Department;
use App\Models\Organization\Position;
use App\Models\Organization\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class PositionController extends Controller implements HasMiddleware
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
        return view('admin.positions.index', [
            'positions' => Position::with(['department', 'unit'])->orderBy('title')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.positions.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        Position::create($this->validateData($request));

        return redirect()->route('admin.positions.index')->with('status', 'position-created');
    }

    public function edit(Position $position): View
    {
        return view('admin.positions.edit', ['position' => $position] + $this->formOptions());
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $position->update($this->validateData($request, $position));

        return redirect()->route('admin.positions.index')->with('status', 'position-updated');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();

        return redirect()->route('admin.positions.index')->with('status', 'position-deleted');
    }

    protected function validateData(Request $request, ?Position $position = null): array
    {
        return $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    protected function formOptions(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ];
    }
}
