<?php

namespace App\Http\Controllers;

use App\Models\Orders\Order;
use App\Models\Partners\BusinessPartner;
use App\Models\Products\Product;
use App\Models\Settings\LookupValue;
use App\Models\User;
use App\Models\Warehouse\StockMovement;
use App\Models\Warehouse\Warehouse;
use App\Services\Orders\OrderStageService;
use App\Services\Warehouse\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:orders.view', only: ['index', 'show']),
            new Middleware('permission:orders.create', only: ['create', 'store']),
            new Middleware('permission:orders.edit', only: ['edit', 'update', 'saveShipment']),
            new Middleware('permission:orders.delete', only: ['destroy']),
            new Middleware('permission:orders.advance|orders.advance_any', only: ['advance']),
            new Middleware('permission:stock.record', only: ['postStock']),
        ];
    }

    public function index(Request $request, OrderStageService $stageService): View
    {
        $orders = Order::query()
            ->with(['businessPartner', 'currentStage'])
            ->when($request->filled('stage'), fn ($q) => $q->where('current_stage_lookup_value_id', $request->input('stage')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('business_partner_id'), fn ($q) => $q->where('business_partner_id', $request->input('business_partner_id')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('order_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('order_date', '<=', $request->input('date_to')))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'stages' => $stageService->orderedStages(),
            'partners' => BusinessPartner::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('orders.create', $this->formOptions());
    }

    public function store(Request $request, OrderStageService $stageService): RedirectResponse
    {
        $data = $this->validateData($request);
        $items = $data['items'] ?? [];
        unset($data['items']);

        $order = DB::transaction(function () use ($data, $items, $request, $stageService) {
            $order = Order::create($data + [
                'order_number' => $this->generateOrderNumber(),
                'created_by_id' => $request->user()->id,
            ]);

            $this->syncItems($order, $items);

            $stageService->registerInitial($order, $request->user());

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('status', 'order-created');
    }

    public function edit(Order $order): View
    {
        abort_unless($order->isEditable(), 403, 'Only orders still at their initial stage can be edited.');

        return view('orders.edit', ['order' => $order->load('items.product')] + $this->formOptions());
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->isEditable(), 403, 'Only orders still at their initial stage can be edited.');

        $data = $this->validateData($request, $order);
        $items = $data['items'] ?? [];
        unset($data['items']);

        DB::transaction(function () use ($order, $data, $items) {
            $order->update($data);
            $order->items()->delete();
            $this->syncItems($order, $items);
        });

        return redirect()->route('orders.show', $order)->with('status', 'order-updated');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('orders.index')->with('status', 'order-deleted');
    }

    public function show(Order $order, OrderStageService $stageService): View
    {
        $order->load([
            'businessPartner', 'items.product', 'currentStage', 'createdBy', 'shipment.transportMethod',
            'stageLogs.lookupValue', 'stageLogs.responsibleUser', 'stageLogs.documents',
        ]);

        return view('orders.show', [
            'order' => $order,
            'stages' => $stageService->orderedStages(),
            'nextStage' => $stageService->nextStage($order),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'stockAlreadyPosted' => StockMovement::where('order_id', $order->id)->exists(),
            'transportMethods' => LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', 'transport_method'))
                ->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function saveShipment(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'carrier_name' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'vehicle_plate' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'transport_method_lookup_value_id' => ['nullable', 'exists:lookup_values,id'],
            'departure_date' => ['nullable', 'date'],
            'expected_arrival_date' => ['nullable', 'date'],
            'actual_arrival_date' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->shipment()->updateOrCreate(['order_id' => $order->id], $data);

        return redirect()->route('orders.show', $order)->with('status', 'order-shipmentsaved');
    }

    public function postStock(Request $request, Order $order, StockService $stockService): RedirectResponse
    {
        abort_if(StockMovement::where('order_id', $order->id)->exists(), 422, 'Stock has already been posted for this order.');

        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
        ]);

        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $movementType = $order->businessPartner->type === 'customer' ? 'out' : 'in';

        DB::transaction(function () use ($order, $warehouse, $movementType, $request, $stockService) {
            foreach ($order->items()->whereNotNull('product_id')->with('product')->get() as $item) {
                $stockService->recordMovement($warehouse, $item->product, $movementType, (float) $item->quantity, $request->user(), [
                    'unit_cost' => $item->unit_price,
                    'business_partner_id' => $order->business_partner_id,
                    'order_id' => $order->id,
                    'reference' => $order->order_number,
                ]);
            }
        });

        return redirect()->route('orders.show', $order)->with('status', 'order-stockposted');
    }

    public function advance(Request $request, Order $order, OrderStageService $stageService): RedirectResponse
    {
        $data = $request->validate([
            'lookup_value_id' => ['required', 'exists:lookup_values,id'],
            'responsible_user_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'files.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $target = LookupValue::findOrFail($data['lookup_value_id']);

        $stageService->advance($order, $target, $request->user(), $data, $request->file('files', []));

        return redirect()->route('orders.show', $order)->with('status', 'order-advanced');
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function syncItems(Order $order, array $items): void
    {
        foreach (array_values($items) as $index => $item) {
            $product = ! empty($item['sku']) ? Product::where('sku', $item['sku'])->first() : null;

            $order->items()->create([
                'product_id' => $product?->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? null,
                'packaging' => $item['packaging'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                'line_total' => isset($item['unit_price']) ? round($item['quantity'] * $item['unit_price'], 2) : null,
                'sort_order' => $index,
            ]);
        }
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Y').'-'.str_pad((string) (Order::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT);
    }

    protected function validateData(Request $request, ?Order $order = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['internal', 'external'])],
            'order_date' => ['required', 'date'],
            'business_partner_id' => ['required', 'exists:business_partners,id'],
            'currency' => ['required', 'string', 'size:3'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*.sku' => ['nullable', 'string', 'exists:products,sku'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.001'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.packaging' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $partner = BusinessPartner::findOrFail($data['business_partner_id']);
        $expectedTypes = $data['type'] === 'external' ? ['supplier', 'customer'] : ['store', 'branch'];

        if (! in_array($partner->type, $expectedTypes, true)) {
            throw ValidationException::withMessages([
                'business_partner_id' => 'The selected partner type does not match the order type.',
            ]);
        }

        return $data;
    }

    protected function formOptions(): array
    {
        return [
            'partners' => BusinessPartner::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('title')->get(['sku', 'title', 'unit', 'price']),
        ];
    }
}
