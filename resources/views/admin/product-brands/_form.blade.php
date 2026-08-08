@php($brand = $brand ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="title" :value="__('products.field_title')" />
        <x-text-input id="title" name="title" class="mt-1 w-100" :value="old('title', $brand?->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="en_title" :value="__('products.field_en_title')" />
        <x-text-input id="en_title" name="en_title" class="mt-1 w-100" :value="old('en_title', $brand?->en_title)" />
        <x-input-error :messages="$errors->get('en_title')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="country" :value="__('products.field_country')" />
        <x-text-input id="country" name="country" class="mt-1 w-100" :value="old('country', $brand?->country)" />
        <x-input-error :messages="$errors->get('country')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="description" :value="__('products.field_description')" />
        <textarea id="description" name="description" rows="3" class="form-control mt-1">{{ old('description', $brand?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $brand?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-24 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.product-brands.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
