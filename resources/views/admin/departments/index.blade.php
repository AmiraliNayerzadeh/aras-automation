<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.departments') }}</h2>
            @can('organization.create')
                <a href="{{ route('admin.departments.create') }}" class="btn btn-dark btn-sm">{{ __('app.create_new') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_name') }}</th>
                        <th>{{ __('app.field_code') }}</th>
                        <th>{{ __('app.field_branch') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->code }}</td>
                            <td>{{ $department->branch->name }}</td>
                            <td>
                                <span class="badge {{ $department->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $department->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('organization.edit')
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                @endcan
                                @can('organization.delete')
                                    <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('app.delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
