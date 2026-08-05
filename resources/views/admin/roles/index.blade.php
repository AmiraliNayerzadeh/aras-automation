<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.nav_roles') }}</h2>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-dark btn-sm">{{ __('app.create_new') }}</a>
        </div>
    </x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_name') }}</th>
                        <th>{{ __('app.field_permissions') }}</th>
                        <th>{{ __('app.nav_users') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                {{ $role->name }}
                                @if ($role->is_system)
                                    <span class="badge text-bg-light border">system</span>
                                @endif
                            </td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                @unless ($role->is_system)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('app.delete') }}</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
