@php($user = $user ?? null)

<div class="d-flex align-items-center gap-3 mb-24">
    <img src="{{ $user?->avatar_url ?? asset('assets/user-default.jpg') }}" alt="" class="w-80-px h-80-px rounded-circle object-fit-cover border">
    <div class="flex-grow-1">
        <x-input-label for="avatar" :value="__('app.field_avatar')" />
        <input id="avatar" name="avatar" type="file" accept="image/*" class="form-control radius-8 mt-1">
        <x-input-error :messages="$errors->get('avatar')" class="mt-1" />
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="name" :value="__('app.field_name')" />
        <x-text-input id="name" name="name" class="mt-1 w-100" :value="old('name', $user?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="email" :value="__('app.field_email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 w-100" :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="password" :value="__('app.field_password')" />
        <x-text-input id="password" name="password" type="password" class="mt-1 w-100" />
        <div class="form-text">{{ __('app.field_password_hint') }}</div>
        <x-input-error :messages="$errors->get('password')" class="mt-1" />
    </div>

    <div class="col-md-6">
        <x-input-label for="employee_number" :value="__('app.field_employee_number')" />
        <x-text-input id="employee_number" name="employee_number" class="mt-1 w-100" :value="old('employee_number', $user?->employee_number)" />
        <x-input-error :messages="$errors->get('employee_number')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="status" :value="__('app.field_status')" />
        <select id="status" name="status" class="form-select mt-1">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $user?->status?->value ?? 'active') === $status->value)>{{ $status->value }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="employment_type" :value="__('app.field_title')" />
        <select id="employment_type" name="employment_type" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($employmentTypes as $type)
                <option value="{{ $type->value }}" @selected(old('employment_type', $user?->employment_type?->value) === $type->value)>{{ $type->value }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('employment_type')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="locale" :value="__('app.field_locale')" />
        <select id="locale" name="locale" class="form-select mt-1">
            @foreach (['en' => 'English', 'hy' => 'Հայերեն', 'fa' => 'فارسی'] as $code => $label)
                <option value="{{ $code }}" @selected(old('locale', $user?->locale ?? 'en') === $code)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('locale')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="company_id" :value="__('app.field_company')" />
        <select id="company_id" name="company_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected(old('company_id', $user?->company_id) === $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <x-input-label for="branch_id" :value="__('app.field_branch')" />
        <select id="branch_id" name="branch_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', $user?->branch_id) === $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <x-input-label for="department_id" :value="__('app.field_department')" />
        <select id="department_id" name="department_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $user?->department_id) === $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <x-input-label for="unit_id" :value="__('app.field_unit')" />
        <select id="unit_id" name="unit_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $user?->unit_id) === $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <x-input-label for="position_id" :value="__('app.field_position')" />
        <select id="position_id" name="position_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($positions as $position)
                <option value="{{ $position->id }}" @selected(old('position_id', $user?->position_id) === $position->id)>{{ $position->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <x-input-label for="manager_id" :value="__('app.field_manager')" />
        <select id="manager_id" name="manager_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($managers as $manager)
                @continue($user && $manager->id === $user->id)
                <option value="{{ $manager->id }}" @selected(old('manager_id', $user?->manager_id) === $manager->id)>{{ $manager->name }}</option>
            @endforeach
        </select>
    </div>

    @can('roles.manage')
        <div class="col-12">
            <x-input-label :value="__('app.field_role')" />
            <div class="mt-1 d-flex flex-wrap gap-3">
                @foreach ($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}"
                            id="role-{{ $role->id }}"
                            @checked(collect(old('roles', $user?->roles->pluck('name') ?? []))->contains($role->name))>
                        <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    @endcan
</div>

<div class="mt-4 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
