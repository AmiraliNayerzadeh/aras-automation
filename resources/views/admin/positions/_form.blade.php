@php($position = $position ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="department_id" :value="__('app.field_department')" />
        <select id="department_id" name="department_id" class="form-select mt-1" required>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $position?->department_id) === $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('department_id')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="unit_id" :value="__('app.field_unit')" />
        <select id="unit_id" name="unit_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $position?->unit_id) === $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('unit_id')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="title" :value="__('app.field_title')" />
        <x-text-input id="title" name="title" class="mt-1 w-100" :value="old('title', $position?->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="code" :value="__('app.field_code')" />
        <x-text-input id="code" name="code" class="mt-1 w-100" :value="old('code', $position?->code)" required />
        <x-input-error :messages="$errors->get('code')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $position?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
