<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products\Product;
use App\Models\Products\ProductBrand;
use App\Models\Products\ProductCategory;
use App\Models\User;
use App\Models\Warehouse\Warehouse;
use App\Services\Warehouse\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:products.view', only: ['index']),
            new Middleware('permission:products.create', only: ['create', 'store']),
            new Middleware('permission:products.edit', only: ['edit', 'update', 'bulkUpdate']),
            new Middleware('permission:products.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'brand'])
            ->filter(static::filtersFromRequest($request))
            ->orderBy('title')
            ->paginate(24)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => ProductCategory::orderBy('title')->get(),
            'brands' => ProductBrand::orderBy('title')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'view' => $request->input('view') === 'grid' ? 'grid' : 'table',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'category_id' => $request->input('category_id'),
            'brand_id' => $request->input('brand_id'),
            'is_active' => $request->filled('is_active') ? $request->boolean('is_active') : null,
            'stock_status' => $request->input('stock_status'),
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
        ];
    }

    public function create(): View
    {
        return view('admin.products.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $imagePath = $this->storeImage($request);

        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        Product::create($data);

        return redirect()->route('admin.products.index')->with('status', 'product-created');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'activities' => $product->activities()->with('causer')->latest()->limit(20)->get(),
        ] + $this->formOptions());
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateData($request, $product);
        $imagePath = $this->storeImage($request);

        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $product->update($data);

        return redirect()->route('admin.products.edit', $product)->with('status', 'product-updated');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'product-deleted');
    }

    public function bulkUpdate(Request $request, StockService $stockService): RedirectResponse
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['exists:products,id'],
            'action' => ['required', Rule::in(['price_fixed', 'price_percent', 'stock_set', 'stock_adjust', 'field_update'])],
            'amount' => ['nullable', 'numeric'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'brand_id' => ['nullable', 'exists:product_brands,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $products = Product::whereIn('id', $data['product_ids'])->get();
        $actor = $request->user();

        DB::transaction(function () use ($data, $products, $actor, $stockService) {
            foreach ($products as $product) {
                match ($data['action']) {
                    'price_fixed' => $product->update([
                        'price' => max(0, round((float) $product->price + (float) ($data['amount'] ?? 0), 2)),
                    ]),
                    'price_percent' => $product->update([
                        'price' => max(0, round((float) $product->price * (1 + (float) ($data['amount'] ?? 0) / 100), 2)),
                    ]),
                    'stock_set', 'stock_adjust' => $this->applyBulkStock($product, $data, $actor, $stockService),
                    'field_update' => $product->update(array_filter([
                        'category_id' => $data['category_id'] ?? null,
                        'brand_id' => $data['brand_id'] ?? null,
                        'is_active' => array_key_exists('is_active', $data) ? $data['is_active'] : null,
                    ], fn ($value) => $value !== null)),
                };
            }
        });

        return redirect()->route('admin.products.index')->with('status', 'products-bulk-updated');
    }

    protected function applyBulkStock(Product $product, array $data, User $actor, StockService $stockService): void
    {
        if (empty($data['warehouse_id'])) {
            return;
        }

        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $amount = (float) ($data['amount'] ?? 0);

        $delta = $data['action'] === 'stock_set'
            ? $amount - $stockService->balanceFor($product, $warehouse)
            : $amount;

        if ($delta === 0.0) {
            return;
        }

        $stockService->recordMovement($warehouse, $product, 'adjustment', $delta, $actor, ['note' => 'Bulk adjustment']);
    }

    protected function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $request->validate(['image' => ['image', 'max:4096']]);

        return $request->file('image')->store('products', 'public');
    }

    protected function validateData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.($product?->id)],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode,'.($product?->id)],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'brand_id' => ['nullable', 'exists:product_brands,id'],
            'unit' => ['nullable', 'string', 'max:50'],
            'package_quantity' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    protected function formOptions(): array
    {
        return [
            'categories' => ProductCategory::orderBy('title')->get(),
            'brands' => ProductBrand::orderBy('title')->get(),
        ];
    }
}
