<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings\LookupType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class LookupTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.manage'),
        ];
    }

    public function index(): View
    {
        return view('admin.lookup-types.index', [
            'lookupTypes' => LookupType::withCount('values')->orderBy('name')->get(),
        ]);
    }

    public function edit(LookupType $lookupType): View
    {
        return view('admin.lookup-types.edit', [
            'lookupType' => $lookupType->load('values'),
        ]);
    }

    public function storeValue(Request $request, LookupType $lookupType): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'label_en' => ['required', 'string', 'max:255'],
            'label_hy' => ['nullable', 'string', 'max:255'],
            'label_fa' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $lookupType->values()->create([
            'code' => $data['code'],
            'label' => [
                'en' => $data['label_en'],
                'hy' => $data['label_hy'] ?? $data['label_en'],
                'fa' => $data['label_fa'] ?? $data['label_en'],
            ],
            'color' => $data['color'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.lookup-types.edit', $lookupType)->with('status', 'lookup-value-created');
    }
}
