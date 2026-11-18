<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('merchandising.title_stores') }} <span class="text-secondary-light fw-normal text-md">({{ number_format($stores->total()) }})</span></h2>
            <a href="{{ route('merchandising.stores.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm"><i class="ri-add-line"></i> {{ __('app.create_new') }}</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('merchandising.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    @if (session('import_result'))
        @php($result = session('import_result'))
        <div class="alert alert-success radius-12 mb-24">
            <div class="fw-semibold"><i class="ri-checkbox-circle-line"></i> {{ __('merchandising.import_result', $result) }}</div>
            @if ($result['conflicts'])
                <details class="mt-8">
                    <summary class="text-sm">{{ __('merchandising.import_conflicts') }} ({{ count($result['conflicts']) }})</summary>
                    <ul class="text-sm mb-0 mt-8">
                        @foreach (array_slice($result['conflicts'], 0, 50) as $conflict)
                            <li>{{ $conflict }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    @endif

    @if ($unverifiedCount && request('verified') !== '0')
        <div class="alert alert-warning radius-12 mb-24 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="ri-error-warning-line"></i> {{ __('merchandising.unverified_stores', ['count' => $unverifiedCount]) }}</span>
            <a href="{{ route('merchandising.stores.index', ['verified' => '0']) }}" class="btn btn-sm btn-warning radius-8">{{ __('merchandising.show_unverified') }}</a>
        </div>
    @endif

    <div class="row g-3 mb-24">
        <div class="col-xl-8">
            <div class="card radius-12 h-100">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <x-input-label for="q" :value="__('merchandising.store_search')" />
                            <x-text-input id="q" name="q" class="mt-1 w-100" :value="request('q')" />
                        </div>
                        <div class="col-md-2 col-4">
                            <x-input-label for="status" :value="__('merchandising.field_status')" />
                            <select id="status" name="status" class="form-select mt-1">
                                <option value="">{{ __('merchandising.all') }}</option>
                                @foreach (\App\Models\Merchandising\Store::STATUSES as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('merchandising.status_'.$status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-4">
                            <x-input-label for="type" :value="__('merchandising.field_type')" />
                            <select id="type" name="type" class="form-select mt-1">
                                <option value="">{{ __('merchandising.all') }}</option>
                                @foreach (\App\Models\Merchandising\Store::TYPES as $type)
                                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ __('merchandising.type_'.$type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-4">
                            <x-input-label for="region" :value="__('merchandising.field_region')" />
                            <select id="region" name="region" class="form-select mt-1">
                                <option value="">{{ __('merchandising.all') }}</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region }}" @selected(request('region') === $region)>{{ $region }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-primary-600 radius-8 w-100 py-8" title="{{ __('merchandising.filter') }}"><i class="ri-filter-3-line"></i></button>
                        </div>
                        @if (request('verified') === '0')
                            <input type="hidden" name="verified" value="0">
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card radius-12 h-100">
                <div class="card-body">
                    <div class="fw-semibold mb-4"><i class="ri-file-excel-2-line text-success-600"></i> {{ __('merchandising.import_title') }}</div>
                    <p class="text-secondary-light text-xs mb-12">{{ __('merchandising.import_hint') }}</p>
                    <form method="POST" action="{{ route('merchandising.stores.import') }}" enctype="multipart/form-data" class="d-flex gap-2" onsubmit="this.querySelector('button').disabled = true;">
                        @csrf
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control form-control-sm" required>
                        <button class="btn btn-sm btn-success-600 radius-8 px-12 flex-shrink-0">{{ __('merchandising.import_button') }}</button>
                    </form>
                    <x-input-error :messages="$errors->get('file')" class="mt-1" />
                    <a href="{{ route('merchandising.stores.template') }}" class="text-xs text-primary-600 d-inline-block mt-8"><i class="ri-download-2-line"></i> {{ __('merchandising.import_template') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle text-sm">
                <thead>
                    <tr>
                        <th>{{ __('merchandising.field_name') }}</th>
                        <th>{{ __('merchandising.field_codes') }}</th>
                        <th>{{ __('merchandising.field_type') }}</th>
                        <th>{{ __('merchandising.field_region') }}</th>
                        <th>{{ __('merchandising.field_status') }}</th>
                        <th>{{ __('merchandising.field_visits') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stores as $store)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $store->name }}
                                    @if ($store->is_vip) <span class="badge text-xs px-6 py-2 radius-4 text-warning-600 bg-warning-100">VIP</span> @endif
                                    @if (! $store->is_verified) <span class="badge text-xs px-6 py-2 radius-4 text-danger-600 bg-danger-100">{{ __('merchandising.typed_store_badge') }}</span> @endif
                                </div>
                                <div class="text-secondary-light text-xs text-truncate" style="max-width: 360px;">{{ collect([$store->city, $store->address])->filter()->implode(' · ') }}</div>
                            </td>
                            <td class="text-nowrap">{{ $store->codes() ?: '—' }}</td>
                            <td>{{ $store->type ? __('merchandising.type_'.$store->type) : '—' }}</td>
                            <td>{{ $store->region ?? '—' }}</td>
                            <td>
                                <span class="badge text-xs fw-semibold px-10 py-4 radius-4 {{ ['active' => 'text-success-600 bg-success-100', 'suspended' => 'text-warning-600 bg-warning-100', 'inactive' => 'text-neutral-600 bg-neutral-200'][$store->status] ?? '' }}">{{ __('merchandising.status_'.$store->status) }}</span>
                            </td>
                            <td>{{ $store->visits_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('merchandising.stores.edit', array_merge(['store' => $store], request()->only('q', 'page'))) }}" class="btn btn-sm btn-outline-secondary-600 radius-8">{{ __('app.edit') }}</a>
                                <form method="POST" action="{{ route('merchandising.stores.destroy', $store) }}" class="d-inline" onsubmit="return confirm(@js(__('app.confirm_delete')));">
                                    @csrf @method('delete')
                                    <button class="btn btn-sm btn-outline-danger-600 radius-8">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary-light py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-16">{{ $stores->links() }}</div>
</x-app-layout>
