<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Models\Merchandising\Store;
use App\Services\Merchandising\ImportRows;
use App\Services\Merchandising\StoreImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StoreController extends Controller implements HasMiddleware
{
    public const TEMPLATE_COLUMNS = ['name', 'food_code', 'cleaning_code', 'type', 'city', 'region', 'address', 'phone', 'tax_code', 'status', 'vip'];

    public static function middleware(): array
    {
        return [new Middleware('permission:stores.manage')];
    }

    public function index(Request $request): View
    {
        $stores = Store::query()
            ->search($request->input('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->input('region')))
            ->when($request->input('verified') === '0', fn ($q) => $q->where('is_verified', false))
            ->withCount('visits')
            ->orderBy('is_verified')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('merchandising.stores.index', [
            'stores' => $stores,
            'regions' => Store::whereNotNull('region')->distinct()->orderBy('region')->pluck('region'),
            'unverifiedCount' => Store::where('is_verified', false)->count(),
        ]);
    }

    public function create(): View
    {
        return view('merchandising.stores.form', ['store' => new Store(['status' => 'active', 'is_verified' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Store::create($this->validateData($request) + ['created_by' => $request->user()->id]);

        return redirect()->route('merchandising.stores.index')->with('status', 'store-created');
    }

    public function edit(Store $store): View
    {
        return view('merchandising.stores.form', ['store' => $store]);
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $store->update($this->validateData($request, $store));

        return redirect()->route('merchandising.stores.index', $request->only('q', 'page'))->with('status', 'store-updated');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $store->delete();

        return back()->with('status', 'store-deleted');
    }

    public function import(Request $request, StoreImporter $importer): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'extensions:'.ImportRows::EXTENSIONS, 'max:51200']]);
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        try {
            $rows = ImportRows::fromUpload($request->file('file'), ['name', 'acc_code', 'food_acc_code']);
        } catch (\Throwable) {
            return back()->withErrors(['file' => __('merchandising.import_unreadable')]);
        }

        $result = $importer->import($rows, $request->user()->id);

        if ($result['format'] === 'unknown') {
            return back()->withErrors(['file' => __('merchandising.import_unknown_format')]);
        }

        return back()->with('import_result', $result);
    }

    public function template(): BinaryFileResponse
    {
        $example = ['SAS Supermarket Komitas', '11706001', '11810001', 'supermarket', 'Yerevan', 'Komitas', 'Komitas Ave 5', '010 000000', '01234567', 'active', 'no'];

        return Excel::download(new class([self::TEMPLATE_COLUMNS, $example]) implements FromArray, ShouldAutoSize
        {
            public function __construct(private array $rows) {}

            public function array(): array
            {
                return $this->rows;
            }
        }, 'stores-template.xlsx');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?Store $store = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'food_account_code' => ['nullable', 'regex:/^\d{5,12}$/', Rule::unique('stores')->ignore($store)],
            'cleaning_account_code' => ['nullable', 'regex:/^\d{5,12}$/', Rule::unique('stores')->ignore($store)],
            'tax_code' => ['nullable', 'string', 'max:20'],
            'type' => ['nullable', Rule::in(Store::TYPES)],
            'city' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(Store::STATUSES)],
            'is_vip' => ['sometimes', 'boolean'],
            'is_verified' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return $data + ['is_vip' => false, 'is_verified' => false];
    }
}
