<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('warehouse.title_warehouses') }}</h2>
            @can('warehouse.manage')
                <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.create_new') }}</a>
            @endcan
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('warehouse.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('warehouse.field_code') }}</th>
                        <th>{{ __('app.field_name') }}</th>
                        <th>{{ __('products.field_stock') }}</th>
                        <th>{{ __('warehouse.field_default') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($warehouses as $warehouse)
                        <tr>
                            <td><code>{{ $warehouse->code }}</code></td>
                            <td>{{ $warehouse->name }}</td>
                            <td>{{ $warehouse->stock_balances_count }}</td>
                            <td>
                                @if ($warehouse->is_default)
                                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-primary-600 bg-primary-100">{{ __('warehouse.field_default') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $warehouse->is_active ? 'text-success-600 bg-success-100' : 'text-neutral-600 bg-neutral-200' }}">
                                    {{ $warehouse->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('warehouse.manage')
                                    <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.edit') }}</a>
                                    <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
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
