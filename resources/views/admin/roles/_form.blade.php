@php($role = $role ?? null)

<div class="mb-3">
    <x-input-label for="name" :value="__('app.field_name')" />
    <x-text-input id="name" name="name" class="mt-1 w-100" style="max-width: 20rem;" :value="old('name', $role?->name)" required autofocus @readonly($role?->is_system) />
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div class="mb-3">
    <x-input-label :value="__('app.field_permissions')" />
    <div class="mt-1 d-flex flex-wrap gap-3">
        @foreach ($permissions as $permission)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                    id="perm-{{ $permission->id }}"
                    @checked(collect(old('permissions', $role?->permissions->pluck('name') ?? []))->contains($permission->name))>
                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
            </div>
        @endforeach
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('app.cancel') }}</a>
</div>
