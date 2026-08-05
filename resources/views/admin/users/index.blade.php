<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.nav_users') }}</h2>
            @can('create', App\Models\User::class)
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.create_new') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="card radius-12">
        <div class="card-header bg-body">
            <form method="GET" class="d-flex" style="max-width: 20rem;">
                <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('app.field_name') }} / {{ __('app.field_email') }}">
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_name') }}</th>
                        <th>{{ __('app.field_email') }}</th>
                        <th>{{ __('app.field_employee_number') }}</th>
                        <th>{{ __('app.field_department') }}</th>
                        <th>{{ __('app.field_position') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->employee_number ?? '—' }}</td>
                            <td>{{ $user->department?->name ?? '—' }}</td>
                            <td>{{ $user->position?->title ?? '—' }}</td>
                            <td>
                                @php
                                    $statusColor = match ($user->status?->value) {
                                        'active' => 'text-success-600 bg-success-100',
                                        'suspended' => 'text-warning-600 bg-warning-100',
                                        'terminated' => 'text-danger-600 bg-danger-100',
                                        default => 'text-neutral-600 bg-neutral-200',
                                    };
                                @endphp
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $statusColor }}">{{ $user->status?->value }}</span>
                            </td>
                            <td class="text-end">
                                @can('update', $user)
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.edit') }}</a>
                                @endcan
                                @can('delete', $user)
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">{{ __('app.delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-body">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
