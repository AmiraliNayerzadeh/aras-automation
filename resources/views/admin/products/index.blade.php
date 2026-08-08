<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('products.title_index') }}</h2>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="btn btn-sm radius-8 px-12 py-8 {{ $view === 'grid' ? 'btn-primary-600' : 'btn-outline-secondary-600' }}" title="{{ __('products.view_grid') }}">
                        <i class="ri-grid-fill"></i>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}" class="btn btn-sm radius-8 px-12 py-8 {{ $view === 'table' ? 'btn-primary-600' : 'btn-outline-secondary-600' }}" title="{{ __('products.view_table') }}">
                        <i class="ri-list-check-2"></i>
                    </a>
                </div>
                <a href="{{ route('admin.products.export.preview', request()->query()) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-download-2-line"></i> {{ __('exports.title') }}
                </a>
                <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('products.title_categories') }}</a>
                <a href="{{ route('admin.product-brands.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('products.title_brands') }}</a>
                @can('products.create')
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-add-line"></i> {{ __('app.create_new') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('products.flash_'.str_replace('-', '_', str_replace(['products-', 'product-'], '', session('status')))) }}</div>
    @endif

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <x-input-label for="search" :value="__('app.field_name')" />
                    <x-text-input id="search" name="search" class="mt-1 w-100" :value="request('search')" placeholder="{{ __('products.field_title') }} / {{ __('products.field_sku') }} / {{ __('products.field_barcode') }}" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="category_id" :value="__('products.field_category')" />
                    <select id="category_id" name="category_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="brand_id" :value="__('products.field_brand')" />
                    <select id="brand_id" name="brand_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="stock_status" :value="__('products.field_stock_status')" />
                    <select id="stock_status" name="stock_status" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        <option value="in_stock" @selected(request('stock_status') === 'in_stock')>{{ __('products.stock_in_stock') }}</option>
                        <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>{{ __('products.stock_out_of_stock') }}</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <x-input-label for="is_active" :value="__('app.field_status')" />
                    <select id="is_active" name="is_active" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        <option value="1" @selected(request('is_active') === '1')>{{ __('app.field_active') }}</option>
                        <option value="0" @selected(request('is_active') === '0')>{{ __('app.field_inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <x-input-label for="price_min" :value="__('products.field_price_min')" />
                    <x-text-input id="price_min" name="price_min" type="number" step="0.01" class="mt-1 w-100" :value="request('price_min')" />
                </div>
                <div class="col-md-1">
                    <x-input-label for="price_max" :value="__('products.field_price_max')" />
                    <x-text-input id="price_max" name="price_max" type="number" step="0.01" class="mt-1 w-100" :value="request('price_max')" />
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-filter-3-line"></i> {{ __('app.actions') }}
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

    @can('products.edit')
        <div id="bulk-toolbar" class="card radius-12 mb-24 border border-primary-200" style="display: none;">
            <div class="card-body d-flex flex-wrap align-items-center gap-3 py-12">
                <span class="fw-semibold text-primary-600">
                    <i class="ri-checkbox-multiple-line"></i>
                    {{ __('products.bulk_actions_title') }} — <span id="bulk-count">0</span> {{ __('products.bulk_selected_suffix') }}
                </span>
                <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8 px-16 py-8" data-bs-toggle="modal" data-bs-target="#bulk-price-modal">
                    <i class="ri-price-tag-3-line"></i> {{ __('products.action_bulk_price') }}
                </button>
                <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8 px-16 py-8" data-bs-toggle="modal" data-bs-target="#bulk-stock-modal">
                    <i class="ri-archive-2-line"></i> {{ __('products.action_bulk_stock') }}
                </button>
                <button type="button" class="btn btn-outline-primary-600 btn-sm radius-8 px-16 py-8" data-bs-toggle="modal" data-bs-target="#bulk-fields-modal">
                    <i class="ri-edit-2-line"></i> {{ __('products.action_bulk_fields') }}
                </button>
            </div>
        </div>
    @endcan

    @if ($view === 'table')
        <div class="card radius-12">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            @can('products.edit')
                                <th style="width: 30px;"></th>
                            @endcan
                            <th style="width: 64px;">{{ __('products.field_image') }}</th>
                            <th>{{ __('products.field_title') }}</th>
                            <th>{{ __('products.field_sku') }}</th>
                            <th>{{ __('products.field_category') }}</th>
                            <th>{{ __('products.field_brand') }}</th>
                            <th>{{ __('products.field_price') }}</th>
                            <th>{{ __('products.field_stock') }}</th>
                            <th>{{ __('app.field_status') }}</th>
                            <th class="text-end">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php
                                $stock = (float) ($product->stock_balances_sum_quantity ?? 0);
                                $stockColor = $stock > 0 ? 'text-success-600 bg-success-100' : 'text-danger-600 bg-danger-100';
                            @endphp
                            <tr>
                                @can('products.edit')
                                    <td><input type="checkbox" class="form-check-input product-select" value="{{ $product->id }}"></td>
                                @endcan
                                <td>
                                    <div class="bg-neutral-100 radius-8 d-flex align-items-center justify-content-center overflow-hidden" style="width: 48px; height: 48px;">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <i class="ri-image-2-line text-neutral-400"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-primary-light fw-medium">{{ $product->title }}</a>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->category?->title ?? '—' }}</td>
                                <td>{{ $product->brand?->title ?? '—' }}</td>
                                <td>{{ $product->price !== null ? number_format((float) $product->price, 2) : '—' }}</td>
                                <td>
                                    <span class="badge text-sm fw-semibold px-12 py-4 radius-4 {{ $stockColor }}">
                                        {{ rtrim(rtrim(number_format($stock, 2), '0'), '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge text-sm fw-semibold px-12 py-4 radius-4 {{ $product->is_active ? 'text-success-600 bg-success-100' : 'text-neutral-600 bg-neutral-200' }}">
                                        {{ $product->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('products.edit')
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-secondary-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('app.edit') }}</a>
                                    @endcan
                                    @can('products.delete')
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-outline-danger-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('app.delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="row row-cols-xxl-4 row-cols-lg-3 row-cols-md-2 row-cols-1 g-3">
            @forelse ($products as $product)
                @php
                    $stock = (float) ($product->stock_balances_sum_quantity ?? 0);
                    $stockColor = $stock > 0 ? 'text-success-600 bg-success-100' : 'text-danger-600 bg-danger-100';
                @endphp
                <div class="col">
                    <div class="position-relative border radius-16 overflow-hidden h-100 bg-base">
                        @can('products.edit')
                            <div class="position-absolute top-0 start-0 m-8 z-1">
                                <input type="checkbox" class="form-check-input product-select" value="{{ $product->id }}" style="width: 20px; height: 20px;">
                            </div>
                        @endcan

                        <div class="dropdown position-absolute top-0 end-0 me-8 mt-8 z-1">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false" class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center">
                                <i class="ri-more-2-fill"></i>
                            </button>
                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                @can('products.edit')
                                    <li><a class="dropdown-item px-16 py-8 rounded text-secondary-light" href="{{ route('admin.products.edit', $product) }}">{{ __('app.edit') }}</a></li>
                                @endcan
                                @can('products.delete')
                                    <li>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="dropdown-item px-16 py-8 rounded text-danger-600 w-100 text-start border-0 bg-transparent">{{ __('app.delete') }}</button>
                                        </form>
                                    </li>
                                @endcan
                            </ul>
                        </div>

                        <a href="{{ route('admin.products.edit', $product) }}" class="d-block bg-neutral-100 d-flex align-items-center justify-content-center" style="height: 160px;">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <i class="ri-image-2-line text-4xl text-neutral-400"></i>
                            @endif
                        </a>

                        <div class="p-16">
                            <div class="d-flex flex-wrap gap-1 mb-8">
                                @if ($product->category)
                                    <span class="badge text-sm fw-semibold px-12 py-4 radius-4 text-info-600 bg-info-100">{{ $product->category->title }}</span>
                                @endif
                                @if ($product->brand)
                                    <span class="badge text-sm fw-semibold px-12 py-4 radius-4 text-primary-600 bg-primary-100">{{ $product->brand->title }}</span>
                                @endif
                                <span class="badge text-sm fw-semibold px-12 py-4 radius-4 {{ $stockColor }}">
                                    {{ rtrim(rtrim(number_format($stock, 2), '0'), '.') }} {{ __('products.field_stock') }}
                                </span>
                            </div>

                            <h6 class="text-md mb-4 text-truncate" title="{{ $product->title }}">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-primary-light">{{ $product->title }}</a>
                            </h6>
                            <span class="text-secondary-light text-sm d-block mb-8">{{ __('products.field_sku') }}: {{ $product->sku }}</span>

                            @if ($product->barcode)
                                <div class="mb-8" style="max-width: 100%; overflow: hidden;">
                                    {!! \App\Support\Barcode::svg($product->barcode, 1, 28) !!}
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold text-lg">{{ $product->price !== null ? number_format((float) $product->price, 2) : '—' }}</span>
                                @unless ($product->is_active)
                                    <span class="badge text-sm fw-semibold px-12 py-4 radius-4 text-neutral-600 bg-neutral-200">{{ __('app.field_inactive') }}</span>
                                @endunless
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card radius-12">
                        <div class="card-body text-center text-muted py-4">{{ __('app.no_records') }}</div>
                    </div>
                </div>
            @endforelse
        </div>
    @endif

    <div class="mt-24">
        {{ $products->links() }}
    </div>

    @can('products.edit')
        {{-- Bulk price modal --}}
        <div class="modal fade" id="bulk-price-modal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.products.bulk-update') }}" class="modal-content bulk-form">
                    @csrf
                    <input type="hidden" name="action" value="price_fixed" class="bulk-price-action">
                    <div class="bulk-product-ids"></div>
                    <div class="modal-header">
                        <h6 class="modal-title">{{ __('products.action_bulk_price') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <x-input-label :value="__('products.field_price_mode')" />
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input type="radio" name="price_mode" id="price_mode_fixed" value="price_fixed" class="form-check-input" checked>
                                    <label for="price_mode_fixed" class="form-check-label">{{ __('products.price_mode_fixed') }}</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="price_mode" id="price_mode_percent" value="price_percent" class="form-check-input">
                                    <label for="price_mode_percent" class="form-check-label">{{ __('products.price_mode_percent') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <x-input-label :value="__('products.field_price_direction')" />
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input type="radio" name="price_direction" id="price_direction_increase" value="increase" class="form-check-input" checked>
                                    <label for="price_direction_increase" class="form-check-label text-success-600">
                                        <i class="ri-arrow-up-line"></i> {{ __('products.price_direction_increase') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="price_direction" id="price_direction_decrease" value="decrease" class="form-check-input">
                                    <label for="price_direction_decrease" class="form-check-label text-danger-600">
                                        <i class="ri-arrow-down-line"></i> {{ __('products.price_direction_decrease') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1">
                            <x-input-label for="bulk_price_amount" :value="__('products.field_amount')" />
                            <x-text-input id="bulk_price_amount" name="amount" type="number" step="0.01" min="0" class="mt-1 w-100" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <x-primary-button>{{ __('app.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bulk stock modal --}}
        <div class="modal fade" id="bulk-stock-modal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.products.bulk-update') }}" class="modal-content bulk-form">
                    @csrf
                    <input type="hidden" name="action" value="stock_set" class="bulk-stock-action">
                    <div class="bulk-product-ids"></div>
                    <div class="modal-header">
                        <h6 class="modal-title">{{ __('products.action_bulk_stock') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <x-input-label for="bulk_stock_warehouse" :value="__('products.field_warehouse')" />
                            <select id="bulk_stock_warehouse" name="warehouse_id" class="form-select mt-1" required>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <x-input-label :value="__('products.field_stock_mode')" />
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input type="radio" name="stock_mode" id="stock_mode_set" value="stock_set" class="form-check-input" checked>
                                    <label for="stock_mode_set" class="form-check-label">{{ __('products.stock_mode_set') }}</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="stock_mode" id="stock_mode_adjust" value="stock_adjust" class="form-check-input">
                                    <label for="stock_mode_adjust" class="form-check-label">{{ __('products.stock_mode_adjust') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-1">
                            <x-input-label for="bulk_stock_amount" :value="__('products.field_amount')" />
                            <x-text-input id="bulk_stock_amount" name="amount" type="number" step="0.001" class="mt-1 w-100" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <x-primary-button>{{ __('app.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bulk fields modal --}}
        <div class="modal fade" id="bulk-fields-modal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.products.bulk-update') }}" class="modal-content bulk-form">
                    @csrf
                    <input type="hidden" name="action" value="field_update">
                    <div class="bulk-product-ids"></div>
                    <div class="modal-header">
                        <h6 class="modal-title">{{ __('products.action_bulk_fields') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-secondary-light text-sm">{{ __('products.bulk_fields_hint') }}</p>
                        <div class="mb-3">
                            <x-input-label for="bulk_category_id" :value="__('products.field_category')" />
                            <select id="bulk_category_id" name="category_id" class="form-select mt-1">
                                <option value="">—</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <x-input-label for="bulk_brand_id" :value="__('products.field_brand')" />
                            <select id="bulk_brand_id" name="brand_id" class="form-select mt-1">
                                <option value="">—</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <x-input-label for="bulk_is_active" :value="__('app.field_status')" />
                            <select id="bulk_is_active" name="is_active" class="form-select mt-1">
                                <option value="">—</option>
                                <option value="1">{{ __('app.field_active') }}</option>
                                <option value="0">{{ __('app.field_inactive') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <x-primary-button>{{ __('app.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                var checkboxes = document.querySelectorAll('.product-select');
                var toolbar = document.getElementById('bulk-toolbar');
                var countLabel = document.getElementById('bulk-count');

                function selectedIds() {
                    return Array.from(checkboxes).filter(function (c) { return c.checked; }).map(function (c) { return c.value; });
                }

                function refreshToolbar() {
                    var ids = selectedIds();
                    toolbar.style.display = ids.length ? 'flex' : 'none';
                    countLabel.textContent = ids.length;
                }

                checkboxes.forEach(function (cb) { cb.addEventListener('change', refreshToolbar); });

                document.querySelectorAll('.bulk-form').forEach(function (form) {
                    form.addEventListener('submit', function () {
                        var container = form.querySelector('.bulk-product-ids');
                        container.innerHTML = '';
                        selectedIds().forEach(function (id) {
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'product_ids[]';
                            input.value = id;
                            container.appendChild(input);
                        });

                        var directionRadio = form.querySelector('input[name="price_direction"]:checked');
                        var amountInput = form.querySelector('#bulk_price_amount');
                        if (directionRadio && amountInput && amountInput.value !== '') {
                            var magnitude = Math.abs(parseFloat(amountInput.value));
                            amountInput.value = directionRadio.value === 'decrease' ? -magnitude : magnitude;
                        }
                    });
                });

                var priceAction = document.querySelector('.bulk-price-action');
                document.querySelectorAll('input[name="price_mode"]').forEach(function (radio) {
                    radio.addEventListener('change', function () { priceAction.value = radio.value; });
                });

                var stockAction = document.querySelector('.bulk-stock-action');
                document.querySelectorAll('input[name="stock_mode"]').forEach(function (radio) {
                    radio.addEventListener('change', function () { stockAction.value = radio.value; });
                });
            })();
        </script>
    @endcan
</x-app-layout>
