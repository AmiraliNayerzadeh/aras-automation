<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partners\BusinessPartner;
use App\Models\Products\Product;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\Warehouse;
use App\Services\Warehouse\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockMovementController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:stock.view', only: ['index', 'overview']),
            new Middleware('permission:stock.record', only: ['create', 'store']),
        ];
    }

    public function index(Request $request): View
    {
        $movements = StockMovement::query()
            ->with(['warehouse', 'product', 'createdBy', 'businessPartner'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->input('warehouse_id')))
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->input('product_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('occurred_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('occurred_at', '<=', $request->input('date_to')))
            ->latest('occurred_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.stock-movements.index', [
            'movements' => $movements,
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.stock-movements.create', [
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'partners' => BusinessPartner::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, StockService $stockService): RedirectResponse
    {
        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'sku' => ['required', 'exists:products,sku'],
            'type' => ['required', Rule::in(['in', 'out', 'adjustment'])],
            'quantity' => ['required', 'numeric'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'business_partner_id' => ['nullable', 'exists:business_partners,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $product = Product::where('sku', $data['sku'])->firstOrFail();

        $stockService->recordMovement($warehouse, $product, $data['type'], (float) $data['quantity'], $request->user(), $data);

        return redirect()->route('admin.stock-movements.index')->with('status', 'movement-recorded');
    }

    public function overview(Request $request): View
    {
        $warehouseId = $request->input('warehouse_id');

        $stats = [
            'total_products' => Product::count(),
            'total_value' => (float) DB::table('warehouse_stock_balances')
                ->join('products', 'products.id', '=', 'warehouse_stock_balances.product_id')
                ->when($warehouseId, fn ($q) => $q->where('warehouse_stock_balances.warehouse_id', $warehouseId))
                ->sum(DB::raw('warehouse_stock_balances.quantity * COALESCE(products.price, 0)')),
            'warehouses_count' => Warehouse::where('is_active', true)->count(),
        ];

        $products = Product::query()
            ->with(['category'])
            ->withSum(['stockBalances as stock_total' => fn ($q) => $q->when($warehouseId, fn ($qq) => $qq->where('warehouse_id', $warehouseId))], 'quantity')
            ->when($request->string('search')->toString(), fn ($q, $search) => $q->where(function ($qq) use ($search) {
                $qq->where('title', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            }))
            ->orderBy('stock_total')
            ->paginate(20)
            ->withQueryString();

        $stats['out_of_stock'] = Product::query()
            ->withSum(['stockBalances as stock_total' => fn ($q) => $q->when($warehouseId, fn ($qq) => $qq->where('warehouse_id', $warehouseId))], 'quantity')
            ->having(DB::raw('COALESCE(stock_total, 0)'), '<=', 0)
            ->count();

        return view('admin.stock-movements.overview', [
            'products' => $products,
            'stats' => $stats,
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }
}
