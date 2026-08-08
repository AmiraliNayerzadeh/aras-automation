<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('warehouse.title_stock_overview') }}</h2>
            <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                <i class="ri-file-list-3-line"></i> {{ __('warehouse.title_stock_movements') }}
            </a>
        </div>
    </x-slot>

    <div class="row row-cols-xxl-4 row-cols-sm-2 row-cols-1 g-3 mb-24">
        <div class="col">
            <div class="card shadow-none border bg-gradient-start-1 h-100 radius-12">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">{{ __('warehouse.stat_total_products') }}</p>
                            <h6 class="mb-0">{{ number_format($stats['total_products']) }}</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                            <i class="ri-shopping-bag-3-line text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-none border bg-gradient-start-2 h-100 radius-12">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">{{ __('warehouse.stat_total_value') }}</p>
                            <h6 class="mb-0">{{ number_format($stats['total_value'], 2) }}</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                            <i class="ri-money-dollar-circle-line text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-none border bg-gradient-start-3 h-100 radius-12">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">{{ __('warehouse.stat_out_of_stock') }}</p>
                            <h6 class="mb-0">{{ number_format($stats['out_of_stock']) }}</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-danger-main rounded-circle d-flex justify-content-center align-items-center">
                            <i class="ri-alert-line text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-none border bg-gradient-start-4 h-100 radius-12">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">{{ __('warehouse.stat_warehouses') }}</p>
                            <h6 class="mb-0">{{ number_format($stats['warehouses_count']) }}</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                            <i class="ri-home-8-line text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <x-input-label for="search" :value="__('app.field_name')" />
                    <x-text-input id="search" name="search" class="mt-1 w-100" :value="request('search')" placeholder="{{ __('products.field_title') }} / {{ __('warehouse.field_sku') }}" />
                </div>
                <div class="col-md-4">
                    <x-input-label for="warehouse_id" :value="__('warehouse.title_warehouses')" />
                    <select id="warehouse_id" name="warehouse_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 py-8">
                        <i class="ri-filter-3-line"></i> {{ __('app.actions') }}
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
                        <th>{{ __('warehouse.field_product') }}</th>
                        <th>{{ __('products.field_category') }}</th>
                        <th>{{ __('warehouse.field_quantity') }}</th>
                        <th>{{ __('products.field_price') }}</th>
                        <th>{{ __('warehouse.stat_total_value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $stock = (float) ($product->stock_total ?? 0);
                            $stockColor = $stock > 0 ? 'text-success-600 bg-success-100' : 'text-danger-600 bg-danger-100';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $product->title }}</div>
                                <div class="text-secondary-light text-xs">{{ $product->sku }}</div>
                            </td>
                            <td>{{ $product->category?->title ?? '—' }}</td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $stockColor }}">
                                    {{ rtrim(rtrim(number_format($stock, 2), '0'), '.') }}
                                </span>
                            </td>
                            <td>{{ $product->price !== null ? number_format((float) $product->price, 2) : '—' }}</td>
                            <td>{{ number_format($stock * (float) ($product->price ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-24">
        {{ $products->links() }}
    </div>
</x-app-layout>
