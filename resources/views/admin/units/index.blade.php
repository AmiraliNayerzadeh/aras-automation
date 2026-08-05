<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.units') }}</h2>
            @can('organization.create')
                <a href="{{ route('admin.units.create') }}" class="btn btn-dark btn-sm">{{ __('app.create_new') }}</a>
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
                        <th>{{ __('app.field_department') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $unit)
                        <tr>
                            <td>{{ $unit->name }}</td>
                            <td>{{ $unit->code }}</td>
                            <td>{{ $unit->department->name }}</td>
                            <td>
                                <span class="badge {{ $unit->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $unit->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('organization.edit')
                                    <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.edit') }}</a>
                                @endcan
                                @can('organization.delete')
                                    <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
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
