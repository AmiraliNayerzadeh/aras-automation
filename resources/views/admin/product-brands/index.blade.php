<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('products.title_brands') }}</h2>
            @can('products.create')
                <a href="{{ route('admin.product-brands.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.create_new') }}</a>
            @endcan
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('products.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('products.field_title') }}</th>
                        <th>{{ __('products.field_country') }}</th>
                        <th>{{ __('products.title_index') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr>
                            <td>{{ $brand->title }}</td>
                            <td>{{ $brand->country ?? '—' }}</td>
                            <td>{{ $brand->products_count }}</td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $brand->is_active ? 'text-success-600 bg-success-100' : 'text-neutral-600 bg-neutral-200' }}">
                                    {{ $brand->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('products.edit')
                                    <a href="{{ route('admin.product-brands.edit', $brand) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.edit') }}</a>
                                @endcan
                                @can('products.delete')
                                    <form action="{{ route('admin.product-brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">{{ __('app.delete') }}</button>
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

    <div class="mt-4">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.back') }}</a>
    </div>
</x-app-layout>
