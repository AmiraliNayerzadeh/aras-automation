<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('assets.title_index') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.asset-categories.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-price-tag-3-line"></i> {{ __('assets.title_categories') }}
                </a>
                @can('assets.create')
                    <a href="{{ route('admin.assets.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.create_new') }}</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('assets.flash_'.session('status')) }}</div>
    @endif

    @php
        $statusBadge = fn (string $status) => match ($status) {
            'in_use' => 'text-success-600 bg-success-100',
            'in_storage' => 'text-info-600 bg-info-100',
            'under_repair' => 'text-warning-600 bg-warning-100',
            'lost' => 'text-danger-600 bg-danger-100',
            default => 'text-neutral-600 bg-neutral-200',
        };
    @endphp

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <x-input-label for="search" :value="__('app.field_name')" />
                    <x-text-input id="search" name="search" class="mt-1 w-100" :value="request('search')" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="category_id" :value="__('assets.field_category')" />
                    <select id="category_id" name="category_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="status" :value="__('assets.field_status')" />
                    <select id="status" name="status" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('assets.status_'.$status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="current_holder_id" :value="__('assets.field_current_holder')" />
                    <select id="current_holder_id" name="current_holder_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($holders as $holder)
                            <option value="{{ $holder->id }}" @selected(request('current_holder_id') == $holder->id)>{{ $holder->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 py-8">
                        <i class="ri-filter-3-line"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.assets.labels') }}" target="_blank" id="labels-form">
        <div class="card radius-12">
            <div class="card-header bg-body d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" id="select-all" class="form-check-input">
                    <label for="select-all" class="form-check-label">{{ __('app.actions') }}</label>
                </div>
                <button type="submit" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-printer-line"></i> {{ __('assets.action_print_selected') }}
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 32px"></th>
                            <th>{{ __('assets.field_asset_code') }}</th>
                            <th>{{ __('assets.field_title') }}</th>
                            <th>{{ __('assets.field_category') }}</th>
                            <th>{{ __('assets.field_status') }}</th>
                            <th>{{ __('assets.field_current_holder') }}</th>
                            <th class="text-end">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assets as $asset)
                            <tr>
                                <td>
                                    <input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" class="form-check-input asset-checkbox">
                                </td>
                                <td><code>{{ $asset->asset_code }}</code></td>
                                <td>{{ $asset->title }}</td>
                                <td>{{ $asset->category?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $statusBadge($asset->status) }}">
                                        {{ __('assets.status_'.$asset->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($asset->currentHolder)
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $asset->currentHolder->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                                            {{ $asset->currentHolder->name }}
                                        </div>
                                    @else
                                        <span class="text-secondary-light">{{ __('assets.unassigned') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.view') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <div class="mt-24">
        {{ $assets->links() }}
    </div>

    @push('scripts')
        <script>
            document.getElementById('select-all').addEventListener('change', function () {
                document.querySelectorAll('.asset-checkbox').forEach(function (cb) {
                    cb.checked = this.checked;
                }.bind(this));
            });
        </script>
    @endpush
</x-app-layout>
