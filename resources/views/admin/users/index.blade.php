<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.nav_users') }}</h2>
            @can('create', App\Models\User::class)
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-add-line"></i> {{ __('app.create_new') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <x-input-label for="search" :value="__('app.field_name')" />
                    <x-text-input id="search" name="search" class="mt-1 w-100" :value="request('search')" placeholder="{{ __('app.field_name') }} / {{ __('app.field_email') }}" />
                </div>
                <div class="col-md-3">
                    <x-input-label for="department_id" :value="__('app.field_department')" />
                    <select id="department_id" name="department_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="status" :value="__('app.field_status')" />
                    <select id="status" name="status" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="role" :value="__('app.field_role')" />
                    <select id="role" name="role" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 py-8">
                        <i class="ri-filter-3-line"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 g-3">
        @forelse ($users as $user)
            @php
                $gradient = 'bg-gradient-start-'.(($loop->iteration % 5) + 1);
                $statusColor = match ($user->status?->value) {
                    'active' => 'text-success-600 bg-success-100',
                    'suspended' => 'text-warning-600 bg-warning-100',
                    'terminated' => 'text-danger-600 bg-danger-100',
                    default => 'text-neutral-600 bg-neutral-200',
                };
            @endphp
            <div class="col">
                <div class="position-relative border radius-16 overflow-hidden h-100 bg-base">
                    <div class="{{ $gradient }}" style="height: 70px;"></div>

                    <div class="dropdown position-absolute top-0 end-0 me-12 mt-12">
                        <button type="button" data-bs-toggle="dropdown" aria-expanded="false" class="bg-white-gradient-light w-32-px h-32-px radius-8 border border-light-white d-flex justify-content-center align-items-center text-white">
                            <i class="ri-more-2-fill"></i>
                        </button>
                        <ul class="dropdown-menu p-12 border bg-base shadow">
                            @can('update', $user)
                                <li>
                                    <a class="dropdown-item px-16 py-8 rounded text-secondary-light" href="{{ route('admin.users.edit', $user) }}">{{ __('app.edit') }}</a>
                                </li>
                            @endcan
                            @can('delete', $user)
                                <li>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="dropdown-item px-16 py-8 rounded text-danger-600 w-100 text-start border-0 bg-transparent">{{ __('app.delete') }}</button>
                                    </form>
                                </li>
                            @endcan
                        </ul>
                    </div>

                    <div class="ps-16 pb-16 pe-16 text-center" style="margin-top: -35px;">
                        <img src="{{ $user->avatar_url }}" alt="" class="border border-white border-width-2-px w-72-px h-72-px rounded-circle object-fit-cover">
                        <h6 class="text-lg mb-0 mt-8">{{ $user->name }}</h6>
                        <span class="text-secondary-light text-sm mb-12 d-block">{{ $user->email }}</span>

                        <div class="d-flex align-items-center gap-2 bg-neutral-50 radius-8 p-8 mb-12">
                            <div class="text-center w-50">
                                <h6 class="text-sm mb-0 text-truncate">{{ $user->department?->name ?? '—' }}</h6>
                                <span class="text-secondary-light text-xs mb-0">{{ __('app.field_department') }}</span>
                            </div>
                            <div class="text-center w-50">
                                <h6 class="text-sm mb-0 text-truncate">{{ $user->position?->title ?? '—' }}</h6>
                                <span class="text-secondary-light text-xs mb-0">{{ __('app.field_position') }}</span>
                            </div>
                        </div>

                        <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $statusColor }} mb-12 d-inline-block">
                            {{ $user->status?->value }}
                        </span>

                        <a href="{{ route('admin.users.edit', $user) }}" class="btn bg-primary-50 text-primary-600 btn-sm px-12 py-8 radius-8 d-flex align-items-center justify-content-center gap-2 w-100 fw-medium">
                            {{ __('app.view') }}
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card radius-12">
                    <div class="card-body text-center text-muted py-4">{{ __('app.no_records') }}</div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-24">
        {{ $users->links() }}
    </div>
</x-app-layout>
