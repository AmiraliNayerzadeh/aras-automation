<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization\Branch;
use App\Models\Organization\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class BranchController extends Controller implements HasMiddleware
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
        return view('admin.branches.index', [
            'branches' => Branch::with('company')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.branches.create', [
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Branch::create($this->validateData($request));

        return redirect()->route('admin.branches.index')->with('status', 'branch-created');
    }

    public function edit(Branch $branch): View
    {
        return view('admin.branches.edit', [
            'branch' => $branch,
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $branch->update($this->validateData($request, $branch));

        return redirect()->route('admin.branches.index')->with('status', 'branch-updated');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()->route('admin.branches.index')->with('status', 'branch-deleted');
    }

    protected function validateData(Request $request, ?Branch $branch = null): array
    {
        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
