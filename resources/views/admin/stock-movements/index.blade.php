<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('warehouse.title_stock_movements') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.stock-movements.overview') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-bar-chart-2-line"></i> {{ __('warehouse.title_stock_overview') }}
                </a>
                @can('stock.record')
                    <a href="{{ route('admin.stock-movements.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-add-line"></i> {{ __('warehouse.title_record_movement') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('warehouse.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <x-input-label for="warehouse_id" :value="__('warehouse.title_warehouses')" />
                    <select id="warehouse_id" name="warehouse_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="type" :value="__('warehouse.field_type')" />
                    <select id="type" name="type" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        <option value="in" @selected(request('type') === 'in')>{{ __('warehouse.type_in') }}</option>
                        <option value="out" @selected(request('type') === 'out')>{{ __('warehouse.type_out') }}</option>
                        <option value="adjustment" @selected(request('type') === 'adjustment')>{{ __('warehouse.type_adjustment') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_from" :value="__('orders.field_date_from')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 w-100" :value="request('date_from')" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_to" :value="__('orders.field_date_to')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 w-100" :value="request('date_to')" />
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 py-8">
                        <i class="ri-filter-3-line"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('warehouse.field_occurred_at') }}</th>
                        <th>{{ __('warehouse.field_product') }}</th>
                        <th>{{ __('warehouse.title_warehouses') }}</th>
                        <th>{{ __('warehouse.field_type') }}</th>
                        <th>{{ __('warehouse.field_quantity') }}</th>
                        <th>{{ __('warehouse.field_recorded_by') }}</th>
                        <th>{{ __('warehouse.field_reference') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        @php
                            $typeColor = match ($movement->type) {
                                'in' => 'text-success-600 bg-success-100',
                                'out' => 'text-danger-600 bg-danger-100',
                                default => 'text-warning-600 bg-warning-100',
                            };
                        @endphp
                        <tr>
                            <td>{{ $movement->occurred_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="fw-medium">{{ $movement->product->title }}</div>
                                <div class="text-secondary-light text-xs">{{ $movement->product->sku }}</div>
                            </td>
                            <td>{{ $movement->warehouse->name }}</td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $typeColor }}">
                                    {{ __('warehouse.type_'.$movement->type) }}
                                </span>
                            </td>
                            <td>{{ rtrim(rtrim(number_format((float) $movement->quantity, 3), '0'), '.') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $movement->createdBy?->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                                    {{ $movement->createdBy?->name }}
                                </div>
                            </td>
                            <td>{{ $movement->reference ?? ($movement->order_id ? '#'.$movement->order_id : '—') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-24">
        {{ $movements->links() }}
    </div>
</x-app-layout>
