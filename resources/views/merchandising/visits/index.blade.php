<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('merchandising.title_visits') }}</h2>
            <div class="d-flex gap-2 flex-wrap">
                @can('merchandising.view_all')
                    <a href="{{ route('merchandising.reports.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm"><i class="ri-bar-chart-box-line"></i> {{ __('merchandising.nav_reports') }}</a>
                @endcan
                @can('merchandising.create')
                    <a href="{{ route('merchandising.visits.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm"><i class="ri-add-line"></i> {{ __('merchandising.nav_new_visit') }}</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'visit-deleted')
        <div class="alert alert-success radius-8 mb-24">{{ __('merchandising.flash_visit_deleted') }}</div>
    @endif

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3 col-6">
                    <x-input-label for="date_from" :value="__('orders.field_date_from')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 w-100" :value="request('date_from')" />
                </div>
                <div class="col-md-3 col-6">
                    <x-input-label for="date_to" :value="__('orders.field_date_to')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 w-100" :value="request('date_to')" />
                </div>
                @if ($merchandisers->isNotEmpty())
                    <div class="col-md-2">
                        <x-input-label for="user_id" :value="__('merchandising.field_merchandiser')" />
                        <select id="user_id" name="user_id" class="form-select mt-1">
                            <option value="">{{ __('merchandising.all') }}</option>
                            @foreach ($merchandisers as $merchandiser)
                                <option value="{{ $merchandiser->id }}" @selected(request('user_id') == $merchandiser->id)>{{ $merchandiser->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col">
                    <x-input-label for="q" :value="__('merchandising.field_store')" />
                    <x-text-input id="q" name="q" class="mt-1 w-100" :value="request('q')" />
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary-600 radius-8 px-16 py-8"><i class="ri-filter-3-line"></i> {{ __('merchandising.filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('merchandising.field_visited_at') }}</th>
                        <th>{{ __('merchandising.field_store') }}</th>
                        <th>{{ __('merchandising.field_merchandiser') }}</th>
                        <th>{{ __('merchandising.field_products_checked') }}</th>
                        <th>{{ __('merchandising.field_out_of_stock') }}</th>
                        <th>{{ __('merchandising.field_photos') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr class="cursor-pointer" onclick="window.location='{{ route('merchandising.visits.show', $visit) }}'">
                            <td class="text-nowrap">{{ $visit->visited_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('merchandising.visits.show', $visit) }}" class="text-primary-light fw-medium">{{ $visit->store?->name ?? __('merchandising.unlisted_store') }}</a>
                                <div class="text-secondary-light text-xs">{{ $visit->store?->region }}</div>
                            </td>
                            <td>{{ $visit->user?->name }}</td>
                            <td>{{ $visit->items_count }}</td>
                            <td>
                                @if ($visit->out_of_stock_count)
                                    <span class="badge text-xs fw-semibold px-10 py-4 radius-4 text-danger-600 bg-danger-100">{{ $visit->out_of_stock_count }}</span>
                                @else <span class="text-secondary-light">0</span> @endif
                            </td>
                            <td>{{ $visit->media_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary-light py-4">{{ __('merchandising.no_visits') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-16">{{ $visits->links() }}</div>
</x-app-layout>
