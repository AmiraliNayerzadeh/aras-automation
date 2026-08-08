@php($order = $order ?? null)
@php($existingItems = old('items', $order?->items?->toArray() ?? []))

<div class="row g-3">
    <div class="col-md-3">
        <x-input-label for="type" :value="__('orders.field_type')" />
        <select id="type" name="type" class="form-select mt-1" required>
            <option value="internal" @selected(old('type', $order?->type?->value) === 'internal')>{{ __('orders.type_internal') }}</option>
            <option value="external" @selected(old('type', $order?->type?->value) === 'external')>{{ __('orders.type_external') }}</option>
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-1" />
    </div>

    <div class="col-md-3">
        <x-input-label for="order_date" :value="__('orders.field_order_date')" />
        <x-text-input id="order_date" name="order_date" type="date" class="mt-1 w-100" :value="old('order_date', $order?->order_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('order_date')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="business_partner_id" :value="__('orders.field_business_partner')" />
        <select id="business_partner_id" name="business_partner_id" class="form-select mt-1" required>
            <option value="">—</option>
            @foreach ($partners as $partner)
                <option value="{{ $partner->id }}" @selected(old('business_partner_id', $order?->business_partner_id) == $partner->id)>
                    {{ $partner->name }} ({{ __('business-partners.type_'.$partner->type) }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('business_partner_id')" class="mt-1" />
    </div>

    <div class="col-md-3">
        <x-input-label for="currency" :value="__('orders.field_currency')" />
        <x-text-input id="currency" name="currency" class="mt-1 w-100" :value="old('currency', $order?->currency ?? 'USD')" maxlength="3" required />
        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
    </div>

    <div class="col-md-3">
        <x-input-label for="amount" :value="__('orders.field_amount')" />
        <x-text-input id="amount" name="amount" type="number" step="0.01" class="mt-1 w-100" :value="old('amount', $order?->amount)" required />
        <x-input-error :messages="$errors->get('amount')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="description" :value="__('orders.field_description')" />
        <textarea id="description" name="description" rows="2" class="form-control mt-1">{{ old('description', $order?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>
</div>

<hr class="my-24">

<div class="d-flex justify-content-between align-items-center mb-16">
    <h6 class="mb-0">{{ __('orders.items_title') }}</h6>
    <button type="button" id="order-item-add" class="btn btn-outline-primary-600 radius-8 px-16 py-8 text-sm">
        <i class="ri-add-line"></i> {{ __('orders.action_add_item') }}
    </button>
</div>

<div class="table-responsive">
    <table class="table align-middle" id="order-items-table">
        <thead>
            <tr>
                <th style="width: 130px;">{{ __('products.field_sku') }}</th>
                <th>{{ __('orders.field_item_description') }}</th>
                <th style="width: 110px;">{{ __('orders.field_quantity') }}</th>
                <th style="width: 110px;">{{ __('orders.field_unit') }}</th>
                <th style="width: 130px;">{{ __('orders.field_packaging') }}</th>
                <th style="width: 130px;">{{ __('orders.field_unit_price') }}</th>
                <th style="width: 40px;"></th>
            </tr>
        </thead>
        <tbody id="order-items-body">
            @foreach ($existingItems as $item)
                <tr class="order-item-row">
                    <td><input type="text" list="order-product-options" name="items[{{ $loop->index }}][sku]" class="form-control form-control-sm order-item-sku" value="{{ is_array($item) ? ($item['sku'] ?? '') : $item->product?->sku }}" placeholder="{{ __('products.field_sku') }}"></td>
                    <td><input type="text" name="items[{{ $loop->index }}][description]" class="form-control form-control-sm order-item-description" value="{{ is_array($item) ? ($item['description'] ?? '') : $item->description }}" required></td>
                    <td><input type="number" step="0.001" name="items[{{ $loop->index }}][quantity]" class="form-control form-control-sm" value="{{ is_array($item) ? ($item['quantity'] ?? '') : $item->quantity }}" required></td>
                    <td><input type="text" name="items[{{ $loop->index }}][unit]" class="form-control form-control-sm order-item-unit" value="{{ is_array($item) ? ($item['unit'] ?? '') : $item->unit }}"></td>
                    <td><input type="text" name="items[{{ $loop->index }}][packaging]" class="form-control form-control-sm" value="{{ is_array($item) ? ($item['packaging'] ?? '') : $item->packaging }}"></td>
                    <td><input type="number" step="0.0001" name="items[{{ $loop->index }}][unit_price]" class="form-control form-control-sm order-item-price" value="{{ is_array($item) ? ($item['unit_price'] ?? '') : $item->unit_price }}"></td>
                    <td><button type="button" class="btn btn-outline-danger-600 btn-sm order-item-remove"><i class="ri-delete-bin-line"></i></button></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p id="order-items-empty" class="text-center text-muted py-2" @style(['display: none' => count($existingItems) > 0])>{{ __('orders.no_items') }}</p>
</div>

<datalist id="order-product-options">
    @foreach ($products as $product)
        <option value="{{ $product->sku }}">{{ $product->title }}</option>
    @endforeach
</datalist>

<template id="order-item-template">
    <tr class="order-item-row">
        <td><input type="text" list="order-product-options" name="items[__INDEX__][sku]" class="form-control form-control-sm order-item-sku" placeholder="{{ __('products.field_sku') }}"></td>
        <td><input type="text" name="items[__INDEX__][description]" class="form-control form-control-sm order-item-description" required></td>
        <td><input type="number" step="0.001" name="items[__INDEX__][quantity]" class="form-control form-control-sm" required></td>
        <td><input type="text" name="items[__INDEX__][unit]" class="form-control form-control-sm order-item-unit"></td>
        <td><input type="text" name="items[__INDEX__][packaging]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.0001" name="items[__INDEX__][unit_price]" class="form-control form-control-sm order-item-price"></td>
        <td><button type="button" class="btn btn-outline-danger-600 btn-sm order-item-remove"><i class="ri-delete-bin-line"></i></button></td>
    </tr>
</template>

<div class="mt-24 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ $order ? route('orders.show', $order) : route('orders.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>

<script>
    (function () {
        var nextIndex = {{ count($existingItems) }};
        var productsBySku = {!! $products->keyBy('sku')->map(fn ($p) => ['title' => $p->title, 'unit' => $p->unit, 'price' => $p->price])->toJson() !!};

        function toggleEmptyState() {
            var hasRows = document.querySelectorAll('#order-items-body .order-item-row').length > 0;
            document.getElementById('order-items-empty').style.display = hasRows ? 'none' : '';
        }

        function applyProductAutofill(skuInput) {
            var product = productsBySku[skuInput.value];
            if (! product) {
                return;
            }
            var row = skuInput.closest('tr');
            var description = row.querySelector('.order-item-description');
            var unit = row.querySelector('.order-item-unit');
            var price = row.querySelector('.order-item-price');
            if (description && ! description.value) { description.value = product.title; }
            if (unit && ! unit.value) { unit.value = product.unit || ''; }
            if (price && ! price.value) { price.value = product.price || ''; }
        }

        document.getElementById('order-item-add').addEventListener('click', function () {
            var template = document.getElementById('order-item-template').innerHTML.replace(/__INDEX__/g, nextIndex++);
            var tempTable = document.createElement('table');
            tempTable.innerHTML = template;
            document.getElementById('order-items-body').appendChild(tempTable.querySelector('tr'));
            toggleEmptyState();
        });

        document.getElementById('order-items-body').addEventListener('click', function (event) {
            var button = event.target.closest('.order-item-remove');
            if (button) {
                button.closest('tr').remove();
                toggleEmptyState();
            }
        });

        document.getElementById('order-items-body').addEventListener('input', function (event) {
            if (event.target.classList.contains('order-item-sku')) {
                applyProductAutofill(event.target);
            }
        });
    })();
</script>
