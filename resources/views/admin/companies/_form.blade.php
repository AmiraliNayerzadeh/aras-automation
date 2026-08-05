@php($company = $company ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="name" :value="__('app.field_name')" />
        <x-text-input id="name" name="name" class="mt-1 w-100" :value="old('name', $company?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="legal_name" :value="__('app.field_legal_name')" />
        <x-text-input id="legal_name" name="legal_name" class="mt-1 w-100" :value="old('legal_name', $company?->legal_name)" />
        <x-input-error :messages="$errors->get('legal_name')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="code" :value="__('app.field_code')" />
        <x-text-input id="code" name="code" class="mt-1 w-100" :value="old('code', $company?->code)" required />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="timezone" :value="__('app.field_timezone')" />
        <x-text-input id="timezone" name="timezone" class="mt-1 w-100" :value="old('timezone', $company?->timezone ?? 'Asia/Yerevan')" required />
        <x-input-error :messages="$errors->get('timezone')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="default_locale" :value="__('app.field_default_locale')" />
        <select id="default_locale" name="default_locale" class="form-select mt-1">
            @foreach (['en' => 'English', 'hy' => 'Հայերեն', 'fa' => 'فارسی'] as $code => $label)
                <option value="{{ $code }}" @selected(old('default_locale', $company?->default_locale ?? 'en') === $code)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('default_locale')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="phone" :value="__('app.field_phone')" />
        <x-text-input id="phone" name="phone" class="mt-1 w-100" :value="old('phone', $company?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="email" :value="__('app.field_email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 w-100" :value="old('email', $company?->email)" />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="address" :value="__('app.field_address')" />
        <x-text-input id="address" name="address" class="mt-1 w-100" :value="old('address', $company?->address)" />
        <x-input-error :messages="$errors->get('address')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $company?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
