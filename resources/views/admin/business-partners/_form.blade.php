@php($businessPartner = $businessPartner ?? null)

<div class="row g-3">
    <div class="col-md-4">
        <x-input-label for="type" :value="__('business-partners.field_type')" />
        <select id="type" name="type" class="form-select mt-1" required>
            @foreach (['supplier', 'customer', 'store', 'branch'] as $type)
                <option value="{{ $type }}" @selected(old('type', $businessPartner?->type) === $type)>{{ __('business-partners.type_'.$type) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="name" :value="__('app.field_name')" />
        <x-text-input id="name" name="name" class="mt-1 w-100" :value="old('name', $businessPartner?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="legal_name" :value="__('business-partners.field_legal_name')" />
        <x-text-input id="legal_name" name="legal_name" class="mt-1 w-100" :value="old('legal_name', $businessPartner?->legal_name)" />
        <x-input-error :messages="$errors->get('legal_name')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="code" :value="__('app.field_code')" />
        <x-text-input id="code" name="code" class="mt-1 w-100" :value="old('code', $businessPartner?->code)" />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="tax_id" :value="__('business-partners.field_tax_id')" />
        <x-text-input id="tax_id" name="tax_id" class="mt-1 w-100" :value="old('tax_id', $businessPartner?->tax_id)" />
        <x-input-error :messages="$errors->get('tax_id')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="phone" :value="__('app.field_phone')" />
        <x-text-input id="phone" name="phone" class="mt-1 w-100" :value="old('phone', $businessPartner?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="email" :value="__('app.field_email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 w-100" :value="old('email', $businessPartner?->email)" />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div class="col-md-3">
        <x-input-label for="city" :value="__('business-partners.field_city')" />
        <x-text-input id="city" name="city" class="mt-1 w-100" :value="old('city', $businessPartner?->city)" />
        <x-input-error :messages="$errors->get('city')" class="mt-1" />
    </div>

    <div class="col-md-3">
        <x-input-label for="country" :value="__('business-partners.field_country')" />
        <x-text-input id="country" name="country" class="mt-1 w-100" :value="old('country', $businessPartner?->country)" />
        <x-input-error :messages="$errors->get('country')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="address" :value="__('app.field_address')" />
        <x-text-input id="address" name="address" class="mt-1 w-100" :value="old('address', $businessPartner?->address)" />
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="notes" :value="__('business-partners.field_notes')" />
        <textarea id="notes" name="notes" rows="3" class="form-control mt-1">{{ old('notes', $businessPartner?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $businessPartner?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.business-partners.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
