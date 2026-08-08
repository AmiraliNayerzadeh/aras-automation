@php($warehouse = $warehouse ?? null)

<div class="row g-3">
    <div class="col-md-4">
        <x-input-label for="code" :value="__('warehouse.field_code')" />
        <x-text-input id="code" name="code" class="mt-1 w-100" :value="old('code', $warehouse?->code)" required />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div class="col-md-8">
        <x-input-label for="name" :value="__('app.field_name')" />
        <x-text-input id="name" name="name" class="mt-1 w-100" :value="old('name', $warehouse?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div class="col-md-6 form-check">
        <input type="hidden" name="is_default" value="0">
        <input type="checkbox" id="is_default" name="is_default" value="1" class="form-check-input" @checked(old('is_default', $warehouse?->is_default))>
        <label for="is_default" class="form-check-label">{{ __('warehouse.field_default') }}</label>
    </div>

    <div class="col-md-6 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $warehouse?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-24 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
