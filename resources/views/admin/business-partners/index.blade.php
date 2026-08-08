<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('business-partners.title_index') }}</h2>
            @can('business-partners.create')
                <a href="{{ route('admin.business-partners.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-add-line"></i> {{ __('app.create_new') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <x-input-label for="search" :value="__('app.field_name')" />
                    <x-text-input id="search" name="search" class="mt-1 w-100" :value="request('search')" placeholder="{{ __('app.field_name') }} / {{ __('business-partners.field_tax_id') }}" />
                </div>
                <div class="col-md-3">
                    <x-input-label for="type" :value="__('business-partners.field_type')" />
                    <select id="type" name="type" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach (['supplier', 'customer', 'store', 'branch'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ __('business-partners.type_'.$type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="is_active" :value="__('app.field_status')" />
                    <select id="is_active" name="is_active" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        <option value="1" @selected(request('is_active') === '1')>{{ __('app.field_active') }}</option>
                        <option value="0" @selected(request('is_active') === '0')>{{ __('app.field_inactive') }}</option>
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

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_name') }}</th>
                        <th>{{ __('business-partners.field_type') }}</th>
                        <th>{{ __('app.field_code') }}</th>
                        <th>{{ __('app.field_phone') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partners as $partner)
                        <tr>
                            <td>{{ $partner->name }}</td>
                            <td>{{ __('business-partners.type_'.$partner->type) }}</td>
                            <td>{{ $partner->code ?? '—' }}</td>
                            <td>{{ $partner->phone ?? '—' }}</td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $partner->is_active ? 'text-success-600 bg-success-100' : 'text-neutral-600 bg-neutral-200' }}">
                                    {{ $partner->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('business-partners.edit')
                                    <a href="{{ route('admin.business-partners.edit', $partner) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.edit') }}</a>
                                @endcan
                                @can('business-partners.delete')
                                    <form action="{{ route('admin.business-partners.destroy', $partner) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
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

    <div class="mt-24">
        {{ $partners->links() }}
    </div>
</x-app-layout>
