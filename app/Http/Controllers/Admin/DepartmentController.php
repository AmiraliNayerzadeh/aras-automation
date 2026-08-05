<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class DepartmentController extends Controller implements HasMiddleware
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
        return view('admin.departments.index', [
            'departments' => Department::with('branch')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.create', [
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Department::create($this->validateData($request));

        return redirect()->route('admin.departments.index')->with('status', 'department-created');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', [
            'department' => $department,
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validateData($request, $department));

        return redirect()->route('admin.departments.index')->with('status', 'department-updated');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()->route('admin.departments.index')->with('status', 'department-deleted');
    }

    protected function validateData(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
