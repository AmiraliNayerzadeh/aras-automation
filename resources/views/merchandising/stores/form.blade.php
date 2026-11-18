<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ $store->exists ? __('app.edit').' — '.$store->name : __('app.create_new').' — '.__('merchandising.title_stores') }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ $store->exists ? route('merchandising.stores.update', array_merge(['store' => $store], request()->only('q', 'page'))) : route('merchandising.stores.store') }}">
                @csrf
                @if ($store->exists) @method('put') @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-label for="name" :value="__('merchandising.field_name')" />
                        <x-text-input id="name" name="name" class="mt-1 w-100" :value="old('name', $store->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="food_account_code" :value="__('merchandising.field_food_code')" />
                        <x-text-input id="food_account_code" name="food_account_code" inputmode="numeric" class="mt-1 w-100" :value="old('food_account_code', $store->food_account_code)" />
                        <x-input-error :messages="$errors->get('food_account_code')" class="mt-1" />
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="cleaning_account_code" :value="__('merchandising.field_cleaning_code')" />
                        <x-text-input id="cleaning_account_code" name="cleaning_account_code" inputmode="numeric" class="mt-1 w-100" :value="old('cleaning_account_code', $store->cleaning_account_code)" />
                        <x-input-error :messages="$errors->get('cleaning_account_code')" class="mt-1" />
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="type" :value="__('merchandising.field_type')" />
                        <select id="type" name="type" class="form-select mt-1">
                            <option value="">—</option>
                            @foreach (\App\Models\Merchandising\Store::TYPES as $type)
                                <option value="{{ $type }}" @selected(old('type', $store->type) === $type)>{{ __('merchandising.type_'.$type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="status" :value="__('merchandising.field_status')" />
                        <select id="status" name="status" class="form-select mt-1" required>
                            @foreach (\App\Models\Merchandising\Store::STATUSES as $status)
                                <option value="{{ $status }}" @selected(old('status', $store->status) === $status)>{{ __('merchandising.status_'.$status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="city" :value="__('merchandising.field_city')" />
                        <x-text-input id="city" name="city" class="mt-1 w-100" :value="old('city', $store->city)" />
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="region" :value="__('merchandising.field_region')" />
                        <x-text-input id="region" name="region" class="mt-1 w-100" :value="old('region', $store->region)" />
                    </div>
                    <div class="col-md-6">
                        <x-input-label for="address" :value="__('merchandising.field_address')" />
                        <x-text-input id="address" name="address" class="mt-1 w-100" :value="old('address', $store->address)" />
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="phone" :value="__('merchandising.field_phone')" />
                        <x-text-input id="phone" name="phone" class="mt-1 w-100" :value="old('phone', $store->phone)" />
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="tax_code" :value="__('merchandising.field_tax_code')" />
                        <x-text-input id="tax_code" name="tax_code" class="mt-1 w-100" :value="old('tax_code', $store->tax_code)" />
                    </div>
                    <div class="col-12">
                        <x-input-label for="notes" :value="__('merchandising.field_notes')" />
                        <textarea id="notes" name="notes" rows="2" class="form-control mt-1">{{ old('notes', $store->notes) }}</textarea>
                    </div>
                    <div class="col-md-3 form-check ms-12">
                        <input type="hidden" name="is_vip" value="0">
                        <input type="checkbox" id="is_vip" name="is_vip" value="1" class="form-check-input" @checked(old('is_vip', $store->is_vip))>
                        <label for="is_vip" class="form-check-label">{{ __('merchandising.field_vip') }}</label>
                    </div>
                    <div class="col-md-6 form-check ms-12">
                        <input type="hidden" name="is_verified" value="0">
                        <input type="checkbox" id="is_verified" name="is_verified" value="1" class="form-check-input" @checked(old('is_verified', $store->is_verified))>
                        <label for="is_verified" class="form-check-label">{{ __('merchandising.field_verified') }}</label>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-24">
                    <button class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.save') }}</button>
                    <a href="{{ route('merchandising.stores.index', request()->only('q', 'page')) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
