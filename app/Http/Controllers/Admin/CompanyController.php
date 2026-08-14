<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class CompanyController extends Controller implements HasMiddleware
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
        return view('admin.companies.index', [
            'companies' => Company::withCount('branches')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Company::create($this->validateData($request));

        return redirect()->route('admin.companies.index')->with('status', 'company-created');
    }

    public function edit(Company $company): View
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $company->update($this->validateData($request, $company));

        return redirect()->route('admin.companies.index')->with('status', 'company-updated');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.companies.index')->with('status', 'company-deleted');
    }

    protected function validateData(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:companies,code,'.($company?->id)],
            'timezone' => ['required', 'string', 'max:100'],
            'default_locale' => ['required', 'string', 'in:en,hy,fa'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
