<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('warehouse.title_record_movement') }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.stock-movements.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-input-label for="warehouse_id" :value="__('warehouse.title_warehouses')" />
                        <select id="warehouse_id" name="warehouse_id" class="form-select mt-1" required>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('warehouse_id')" class="mt-1" />
                    </div>

                    <div class="col-md-4">
                        <x-input-label for="sku" :value="__('warehouse.field_product')" />
                        <input list="product-options" id="sku" name="sku" class="form-control mt-1" :value="old('sku')" required>
                        <datalist id="product-options">
                            @foreach (\App\Models\Products\Product::orderBy('title')->get(['sku', 'title']) as $product)
                                <option value="{{ $product->sku }}">{{ $product->title }}</option>
                            @endforeach
                        </datalist>
                        <x-input-error :messages="$errors->get('sku')" class="mt-1" />
                    </div>

                    <div class="col-md-4">
                        <x-input-label for="type" :value="__('warehouse.field_type')" />
                        <select id="type" name="type" class="form-select mt-1" required>
                            <option value="in" @selected(old('type') === 'in')>{{ __('warehouse.type_in') }}</option>
                            <option value="out" @selected(old('type') === 'out')>{{ __('warehouse.type_out') }}</option>
                            <option value="adjustment" @selected(old('type') === 'adjustment')>{{ __('warehouse.type_adjustment') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>

                    <div class="col-md-3">
                        <x-input-label for="quantity" :value="__('warehouse.field_quantity')" />
                        <x-text-input id="quantity" name="quantity" type="number" step="0.001" class="mt-1 w-100" :value="old('quantity')" required />
                        <x-input-error :messages="$errors->get('quantity')" class="mt-1" />
                    </div>

                    <div class="col-md-3">
                        <x-input-label for="unit_cost" :value="__('warehouse.field_unit_cost')" />
                        <x-text-input id="unit_cost" name="unit_cost" type="number" step="0.0001" class="mt-1 w-100" :value="old('unit_cost')" />
                        <x-input-error :messages="$errors->get('unit_cost')" class="mt-1" />
                    </div>

                    <div class="col-md-3">
                        <x-input-label for="business_partner_id" :value="__('warehouse.field_business_partner')" />
                        <select id="business_partner_id" name="business_partner_id" class="form-select mt-1">
                            <option value="">—</option>
                            @foreach ($partners as $partner)
                                <option value="{{ $partner->id }}" @selected(old('business_partner_id') == $partner->id)>{{ $partner->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('business_partner_id')" class="mt-1" />
                    </div>

                    <div class="col-md-3">
                        <x-input-label for="reference" :value="__('warehouse.field_reference')" />
                        <x-text-input id="reference" name="reference" class="mt-1 w-100" :value="old('reference')" />
                        <x-input-error :messages="$errors->get('reference')" class="mt-1" />
                    </div>

                    <div class="col-12">
                        <x-input-label for="note" :value="__('warehouse.field_note')" />
                        <textarea id="note" name="note" rows="2" class="form-control mt-1">{{ old('note') }}</textarea>
                        <x-input-error :messages="$errors->get('note')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-24 d-flex gap-2">
                    <x-primary-button>{{ __('app.save') }}</x-primary-button>
                    <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
