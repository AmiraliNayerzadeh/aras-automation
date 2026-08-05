@php($unit = $unit ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="department_id" :value="__('app.field_department')" />
        <select id="department_id" name="department_id" class="form-select mt-1" required>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $unit?->department_id) === $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('department_id')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="name" :value="__('app.field_name')" />
        <x-text-input id="name" name="name" class="mt-1 w-100" :value="old('name', $unit?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="code" :value="__('app.field_code')" />
        <x-text-input id="code" name="code" class="mt-1 w-100" :value="old('code', $unit?->code)" required />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $unit?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.units.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
