<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.positions') }}</h2>
            @can('organization.create')
                <a href="{{ route('admin.positions.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.create_new') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_title') }}</th>
                        <th>{{ __('app.field_code') }}</th>
                        <th>{{ __('app.field_department') }}</th>
                        <th>{{ __('app.field_unit') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($positions as $position)
                        <tr>
                            <td>{{ $position->title }}</td>
                            <td>{{ $position->code }}</td>
                            <td>{{ $position->department->name }}</td>
                            <td>{{ $position->unit?->name ?? '—' }}</td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $position->is_active ? 'text-success-600 bg-success-100' : 'text-neutral-600 bg-neutral-200' }}">
                                    {{ $position->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('organization.edit')
                                    <a href="{{ route('admin.positions.edit', $position) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.edit') }}</a>
                                @endcan
                                @can('organization.delete')
                                    <form action="{{ route('admin.positions.destroy', $position) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">{{ __('app.delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
