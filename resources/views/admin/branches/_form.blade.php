@php($branch = $branch ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="company_id" :value="__('app.field_company')" />
        <select id="company_id" name="company_id" class="form-select mt-1" required>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected(old('company_id', $branch?->company_id) === $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('company_id')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="name" :value="__('app.field_name')" />
        <x-text-input id="name" name="name" class="mt-1 w-100" :value="old('name', $branch?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="code" :value="__('app.field_code')" />
        <x-text-input id="code" name="code" class="mt-1 w-100" :value="old('code', $branch?->code)" required />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="phone" :value="__('app.field_phone')" />
        <x-text-input id="phone" name="phone" class="mt-1 w-100" :value="old('phone', $branch?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="address" :value="__('app.field_address')" />
        <x-text-input id="address" name="address" class="mt-1 w-100" :value="old('address', $branch?->address)" />
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $branch?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('app.cancel') }}</a>
</div>
