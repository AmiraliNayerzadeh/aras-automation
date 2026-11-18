<?php

namespace App\Http\Controllers\Merchandising;

use App\Enums\MerchandisingDivision;
use App\Http\Controllers\Controller;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\User;
use App\Services\Merchandising\ImportRows;
use App\Services\Merchandising\ProductCatalogImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Who merchandises which division, which product group belongs to which
 * division, and re-importing the ERP product master.
 */
class SetupController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:stores.manage')];
    }

    public function index(): View
    {
        return view('merchandising.setup', [
            'merchandisers' => User::role('merchandiser')->where('status', 'active')->orderBy('name')->get(),
            'candidates' => User::where('status', 'active')->whereDoesntHave('roles', fn ($q) => $q->where('name', 'merchandiser'))->orderBy('name')->get(['id', 'name']),
            'categories' => ProductCategory::withCount(['products' => fn ($q) => $q->where('is_active', true)])->orderBy('title')->get(),
            'productCount' => Product::where('is_active', true)->count(),
        ]);
    }

    public function addMerchandiser(Request $request): RedirectResponse
    {
        $data = $request->validate(['user_id' => ['required', 'exists:users,id']]);
        User::findOrFail($data['user_id'])->assignRole(Role::findByName('merchandiser', 'web'));

        return back()->with('status', 'merchandiser-added');
    }

    public function updateMerchandiser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'divisions' => ['nullable', 'array'],
            'divisions.*' => [Rule::in(MerchandisingDivision::values())],
        ]);

        $user->forceFill(['merchandising_divisions' => array_values($data['divisions'] ?? [])])->save();

        return back()->with('status', 'merchandiser-updated');
    }

    public function removeMerchandiser(User $user): RedirectResponse
    {
        $user->removeRole('merchandiser');

        return back()->with('status', 'merchandiser-removed');
    }

    public function updateCategories(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'divisions' => ['array'],
            'divisions.*' => ['nullable', Rule::in(MerchandisingDivision::values())],
        ]);

        foreach ($data['divisions'] ?? [] as $id => $division) {
            ProductCategory::whereKey($id)->update(['division' => $division ?: null]);
        }

        return back()->with('status', 'categories-updated');
    }

    public function importProducts(Request $request, ProductCatalogImporter $importer): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'extensions:'.ImportRows::EXTENSIONS, 'max:102400']]);
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        try {
            $rows = ImportRows::fromUpload($request->file('file'), ['item_main_group_desc', 'item_code', 'code']);
        } catch (\Throwable) {
            return back()->withErrors(['file' => __('merchandising.import_unreadable')]);
        }

        if (! $rows) {
            return back()->withErrors(['file' => __('merchandising.import_unknown_format')]);
        }

        return back()->with('product_import_result', $importer->import($rows));
    }
}
