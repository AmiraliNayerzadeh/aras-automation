<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.branches') }}</h2>
            @can('organization.create')
                <a href="{{ route('admin.branches.create') }}" class="btn btn-dark btn-sm">{{ __('app.create_new') }}</a>
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
                        <th>{{ __('app.field_company') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->code }}</td>
                            <td>{{ $branch->company->name }}</td>
                            <td>
                                <span class="badge {{ $branch->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $branch->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('organization.edit')
                                    <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                @endcan
                                @can('organization.delete')
                                    <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
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
