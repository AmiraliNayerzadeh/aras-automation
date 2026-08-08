<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partners\BusinessPartner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessPartnerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:business-partners.view', only: ['index']),
            new Middleware('permission:business-partners.create', only: ['create', 'store', 'storeContact']),
            new Middleware('permission:business-partners.edit', only: ['edit', 'update']),
            new Middleware('permission:business-partners.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $partners = BusinessPartner::query()
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('tax_id', 'like', "%{$search}%");
            }))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.business-partners.index', ['partners' => $partners]);
    }

    public function create(): View
    {
        return view('admin.business-partners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $partner = BusinessPartner::create($this->validateData($request));

        return redirect()->route('admin.business-partners.edit', $partner)->with('status', 'business-partner-created');
    }

    public function edit(BusinessPartner $businessPartner): View
    {
        return view('admin.business-partners.edit', [
            'businessPartner' => $businessPartner->load('contacts'),
        ]);
    }

    public function update(Request $request, BusinessPartner $businessPartner): RedirectResponse
    {
        $businessPartner->update($this->validateData($request, $businessPartner));

        return redirect()->route('admin.business-partners.index')->with('status', 'business-partner-updated');
    }

    public function destroy(BusinessPartner $businessPartner): RedirectResponse
    {
        $businessPartner->delete();

        return redirect()->route('admin.business-partners.index')->with('status', 'business-partner-deleted');
    }

    public function storeContact(Request $request, BusinessPartner $businessPartner): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $businessPartner->contacts()->create($data);

        return redirect()->route('admin.business-partners.edit', $businessPartner)->with('status', 'business-partner-contact-created');
    }

    protected function validateData(Request $request, ?BusinessPartner $businessPartner = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['supplier', 'customer', 'store', 'branch'])],
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:business_partners,code,'.($businessPartner?->id)],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
