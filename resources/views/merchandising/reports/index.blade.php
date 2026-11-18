<x-app-layout>
    @php
        $query = request()->query();
        $today = now()->toDateString();
        $ranges = [
            'report_today' => [$today, $today],
            'report_yesterday' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'report_this_week' => [now()->startOfWeek()->toDateString(), $today],
            'report_this_month' => [now()->startOfMonth()->toDateString(), $today],
        ];
        $stockColors = ['in_stock' => '#22A75E', 'low' => '#F0911E', 'out_of_stock' => '#EF4444', 'not_listed' => '#9CA3AF'];
    @endphp

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 fw-semibold mb-0">{{ __('merchandising.title_reports') }}</h2>
                <div class="text-secondary-light text-sm">{{ $report->periodLabel() }} · {{ $divisionLabel }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('merchandising.reports.pdf', $query) }}" class="btn btn-danger-600 radius-8 px-16 py-8 text-sm"><i class="ri-file-pdf-2-line"></i> {{ __('merchandising.report_pdf') }}</a>
                <a href="{{ route('merchandising.reports.pdf', $query + ['photos' => 0]) }}" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">{{ __('merchandising.report_pdf_no_photos') }}</a>
                <a href="{{ route('merchandising.reports.excel', $query) }}" class="btn btn-success-600 radius-8 px-16 py-8 text-sm"><i class="ri-file-excel-2-line"></i> {{ __('merchandising.report_excel') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-12">
                @foreach ($ranges as $label => [$from, $to])
                    @php($isActive = $report->from->toDateString() === $from && $report->to->toDateString() === $to)
                    <a href="{{ route('merchandising.reports.index', array_merge($query, ['date_from' => $from, 'date_to' => $to])) }}" class="btn btn-sm radius-8 px-12 {{ $isActive ? 'btn-primary-600' : 'btn-outline-secondary-600' }}">{{ __('merchandising.'.$label) }}</a>
                @endforeach
            </div>
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-lg-2 col-6">
                    <label class="form-label text-sm mb-1" for="date_from">From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $report->from->toDateString() }}" class="form-control">
                </div>
                <div class="col-lg-2 col-6">
                    <label class="form-label text-sm mb-1" for="date_to">To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $report->to->toDateString() }}" class="form-control">
                </div>
                <div class="col-lg-2 col-6">
                    <label class="form-label text-sm mb-1" for="division">{{ __('merchandising.report_division') }}</label>
                    <select id="division" name="division" class="form-select">
                        <option value="">{{ __('merchandising.report_all_divisions') }}</option>
                        @foreach (\App\Enums\MerchandisingDivision::cases() as $division)
                            <option value="{{ $division->value }}" @selected(request('division') === $division->value)>{{ $division->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-6">
                    <label class="form-label text-sm mb-1" for="user_id">{{ __('merchandising.field_merchandiser') }}</label>
                    <select id="user_id" name="user_id" class="form-select">
                        <option value="">{{ __('merchandising.report_all_merchandisers') }}</option>
                        @foreach ($merchandisers as $merchandiser)
                            <option value="{{ $merchandiser->id }}" @selected(request('user_id') == $merchandiser->id)>{{ $merchandiser->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-6">
                    <label class="form-label text-sm mb-1" for="store_type">{{ __('merchandising.field_type') }}</label>
                    <select id="store_type" name="store_type" class="form-select">
                        <option value="">{{ __('merchandising.report_all_types') }}</option>
                        @foreach (\App\Models\Merchandising\Store::TYPES as $type)
                            <option value="{{ $type }}" @selected(request('store_type') === $type)>{{ __('merchandising.type_'.$type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 col-6">
                    <label class="form-label text-sm mb-1" for="region">{{ __('merchandising.field_region') }}</label>
                    <select id="region" name="region" class="form-select">
                        <option value="">{{ __('merchandising.all') }}</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region }}" @selected(request('region') === $region)>{{ $region }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($selectedStore)
                    <input type="hidden" name="store_id" value="{{ $selectedStore->id }}">
                @endif
                <div class="col-lg-1 col-12">
                    <button class="btn btn-primary-600 radius-8 w-100 py-8">{{ __('merchandising.report_apply') }}</button>
                </div>
            </form>
            @if ($selectedStore)
                <div class="mt-12 text-sm">
                    {{ __('merchandising.report_store_filter') }}: <strong>{{ $selectedStore->name }}</strong>
                    <a href="{{ route('merchandising.reports.index', \Illuminate\Support\Arr::except($query, 'store_id')) }}" class="ms-2 text-primary-600">{{ __('merchandising.report_clear_store') }}</a>
                </div>
            @endif
        </div>
    </div>

    @if ($report->visits->isEmpty())
        <div class="card radius-12"><div class="card-body text-center text-secondary-light py-40">
            <i class="ri-inbox-line d-block mb-8" style="font-size: 40px;"></i>{{ __('merchandising.no_data') }}
        </div></div>
    @else
        <div class="mr-kpis mb-24">
            @foreach ([
                ['kpi_visits', $kpis['visits'], 'ri-map-pin-user-line', 'primary'],
                ['kpi_stores', $kpis['stores'], 'ri-store-2-line', 'info'],
                ['kpi_products', $kpis['products_checked'], 'ri-shopping-basket-2-line', 'purple'],
                ['kpi_out_rate', $kpis['out_of_stock_rate'] !== null ? $kpis['out_of_stock_rate'].'%' : '—', 'ri-error-warning-line', 'danger', $kpis['out_of_stock'].' / '.$kpis['products_checked']],
                ['kpi_price_ok', $kpis['price_tag_ok_rate'] !== null ? $kpis['price_tag_ok_rate'].'%' : '—', 'ri-price-tag-3-line', 'success'],
                ['kpi_orders', $kpis['orders_needed'], 'ri-shopping-cart-2-line', 'warning'],
            ] as $kpi)
                <div class="mr-kpi mr-tone-{{ $kpi[3] }}">
                    <span class="mr-kpi__icon"><i class="{{ $kpi[2] }}"></i></span>
                    <div>
                        <div class="mr-kpi__value">{{ is_numeric($kpi[1]) ? number_format($kpi[1]) : $kpi[1] }}</div>
                        <div class="mr-kpi__label">{{ __('merchandising.'.$kpi[0]) }}@isset($kpi[4]) <span class="text-secondary-light">({{ $kpi[4] }})</span>@endisset</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-3 mb-24">
            @if (count($perDay) > 1)
                <div class="col-12">
                    <div class="card radius-12"><div class="card-body">
                        <h3 class="mr-title">{{ __('merchandising.chart_visits_per_day') }}</h3>
                        <div id="chart-days"></div>
                    </div></div>
                </div>
            @endif
            <div class="col-lg-4 col-md-6">
                <div class="card radius-12 h-100"><div class="card-body">
                    <h3 class="mr-title">{{ __('merchandising.chart_stock') }}</h3>
                    <div id="chart-stock"></div>
                </div></div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card radius-12 h-100"><div class="card-body">
                    <h3 class="mr-title">{{ __('merchandising.chart_shelf') }}</h3>
                    <div id="chart-shelf"></div>
                </div></div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card radius-12 h-100"><div class="card-body">
                    <h3 class="mr-title">{{ __('merchandising.chart_price_tags') }}</h3>
                    <div id="chart-price"></div>
                </div></div>
            </div>
            <div class="col-lg-6">
                <div class="card radius-12 h-100"><div class="card-body">
                    <h3 class="mr-title">{{ __('merchandising.chart_issues') }}</h3>
                    @include('merchandising.reports._bars', ['counts' => $issueCounts, 'names' => $labels['issues'], 'color' => '#EF4444'])
                </div></div>
            </div>
            <div class="col-lg-6">
                <div class="card radius-12 h-100"><div class="card-body">
                    <h3 class="mr-title">{{ __('merchandising.chart_actions') }}</h3>
                    @include('merchandising.reports._bars', ['counts' => $actionCounts, 'names' => $labels['actions'], 'color' => '#487FFF'])
                </div></div>
            </div>
            <div class="col-lg-6">
                <div class="card radius-12 h-100"><div class="card-body">
                    <h3 class="mr-title">{{ __('merchandising.table_top_out') }}</h3>
                    @if ($topOut->isEmpty())
                        <p class="text-secondary-light mb-0">{{ __('merchandising.nothing_recorded') }}</p>
                    @else
                        <table class="table table-sm mb-0 text-sm align-middle">
                            <thead><tr><th>{{ __('merchandising.col_product') }}</th><th class="text-end">{{ __('merchandising.col_times') }}</th><th class="text-end">{{ __('merchandising.col_stores') }}</th></tr></thead>
                            <tbody>
                                @foreach ($topOut as $row)
                                    <tr><td><div class="fw-medium">{{ $row['name'] }}</div><div class="text-secondary-light text-xs">{{ $row['line'] }}</div></td><td class="text-end text-danger-600 fw-semibold">{{ $row['count'] }}</td><td class="text-end">{{ $row['stores'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div></div>
            </div>
            <div class="col-lg-6">
                <div class="card radius-12 h-100"><div class="card-body">
                    <h3 class="mr-title">{{ __('merchandising.chart_feedback') }}</h3>
                    @include('merchandising.reports._bars', ['counts' => $feedbackCounts, 'names' => $labels['feedback'], 'color' => '#0E91E9'])

                    @if ($byMerchandiser->count() > 1)
                        <h3 class="mr-title mt-24">{{ __('merchandising.table_by_merchandiser') }}</h3>
                        <table class="table table-sm mb-0 text-sm">
                            <thead><tr><th>{{ __('merchandising.field_merchandiser') }}</th><th class="text-end">{{ __('merchandising.kpi_visits') }}</th><th class="text-end">{{ __('merchandising.col_stores') }}</th><th class="text-end">{{ __('merchandising.kpi_products') }}</th><th class="text-end">{{ __('merchandising.kpi_out_of_stock') }}</th></tr></thead>
                            <tbody>
                                @foreach ($byMerchandiser as $row)
                                    <tr><td>{{ $row['name'] }}</td><td class="text-end">{{ $row['visits'] }}</td><td class="text-end">{{ $row['stores'] }}</td><td class="text-end">{{ $row['products'] }}</td><td class="text-end">{{ $row['out_of_stock'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div></div>
            </div>
        </div>

        <div class="card radius-12">
            <div class="card-header bg-base fw-semibold">{{ __('merchandising.table_visits') }} ({{ $report->visits->count() }})</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle text-sm">
                    <thead><tr><th>{{ __('merchandising.col_time') }}</th><th>{{ __('merchandising.field_store') }}</th><th>{{ __('merchandising.field_merchandiser') }}</th><th class="text-end">{{ __('merchandising.kpi_products') }}</th><th class="text-end">{{ __('merchandising.kpi_out_of_stock') }}</th><th class="text-end">{{ __('merchandising.kpi_photos') }}</th></tr></thead>
                    <tbody>
                        @foreach ($report->visits->sortByDesc('visited_at') as $visit)
                            @php($items = $report->itemsOf($visit))
                            <tr>
                                <td class="text-nowrap">{{ $visit->visited_at->format('M j, H:i') }}</td>
                                <td>
                                    <a href="{{ route('merchandising.visits.show', $visit) }}" class="fw-medium text-primary-light">{{ $visit->store?->name ?? __('merchandising.unlisted_store') }}</a>
                                    @if ($visit->store_id)
                                        <a href="{{ route('merchandising.reports.index', array_merge($query, ['store_id' => $visit->store_id])) }}" class="ms-1 text-secondary-light" title="{{ __('merchandising.report_store_filter') }}"><i class="ri-filter-3-line"></i></a>
                                    @endif
                                    <div class="text-secondary-light text-xs">{{ $visit->store?->region }}</div>
                                </td>
                                <td>{{ $visit->user?->name }}</td>
                                <td class="text-end">{{ $items->count() }}</td>
                                <td class="text-end">{{ $items->whereIn('stock_status', \App\Services\Merchandising\MerchandisingReport::OUT_STATUSES)->count() }}</td>
                                <td class="text-end">{{ $visit->media->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            .mr-kpis { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
            .mr-kpi { --tone: 72, 127, 255; display: flex; align-items: center; gap: 12px; padding: 16px; border-radius: 14px; background: var(--white); border: 1px solid var(--border-color); }
            .mr-kpi__icon { width: 46px; height: 46px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; background: rgba(var(--tone), .12); color: rgb(var(--tone)); flex: 0 0 auto; }
            .mr-kpi__value { font-size: 22px; font-weight: 700; color: var(--text-primary-light); line-height: 1.2; }
            .mr-kpi__label { font-size: 13px; color: var(--text-secondary-light); }
            .mr-tone-primary { --tone: 72, 127, 255; } .mr-tone-info { --tone: 14, 145, 233; } .mr-tone-purple { --tone: 132, 90, 223; }
            .mr-tone-danger { --tone: 239, 68, 68; } .mr-tone-success { --tone: 34, 167, 94; } .mr-tone-warning { --tone: 240, 145, 30; }
            .mr-title { font-size: 15px !important; font-weight: 600; margin-bottom: 12px; color: var(--text-primary-light); }
            .mr-bar { margin-bottom: 10px; }
            .mr-bar__head { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; margin-bottom: 4px; color: var(--text-primary-light); }
            .mr-bar__track { height: 8px; border-radius: 999px; background: var(--neutral-100); overflow: hidden; }
            .mr-bar__fill { height: 100%; border-radius: 999px; }
        </style>
    @endpush

    @if ($report->visits->isNotEmpty())
        @push('scripts')
            <script src="{{ asset('assets/wowdash/js/lib/apexcharts.min.js') }}"></script>
            <script>
                (function () {
                    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
                    var base = { chart: { toolbar: { show: false }, fontFamily: 'inherit', foreColor: dark ? '#D1D5DB' : '#4B5563' }, dataLabels: { enabled: false }, legend: { position: 'bottom' }, tooltip: { theme: dark ? 'dark' : 'light' } };
                    var donut = function (id, labels, values, colors) {
                        if (!values.some(function (v) { return v > 0; })) { document.getElementById(id).innerHTML = '<p class="text-secondary-light mb-0">' + @json(__('merchandising.nothing_recorded')) + '</p>'; return; }
                        new ApexCharts(document.getElementById(id), Object.assign({}, base, {
                            chart: Object.assign({}, base.chart, { type: 'donut', height: 260 }),
                            series: values, labels: labels, colors: colors,
                            plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Total' } } } } },
                            dataLabels: { enabled: true, formatter: function (v) { return Math.round(v) + '%'; } },
                        })).render();
                    };

                    donut('chart-stock', @json(array_values(array_intersect_key($labels['stock'], $stock))), @json(array_values($stock)), @json(array_values(array_intersect_key($stockColors, $stock))));
                    donut('chart-shelf', @json(array_values($labels['shelf'])), @json(array_values($shelf)), ['#22A75E', '#487FFF', '#F0911E', '#845ADF', '#9CA3AF']);
                    donut('chart-price', @json(array_values($labels['price_tag'])), @json(array_values($priceTags)), ['#22A75E', '#EF4444', '#F0911E']);

                    var days = @json($perDay);
                    if (document.getElementById('chart-days')) {
                        new ApexCharts(document.getElementById('chart-days'), Object.assign({}, base, {
                            chart: Object.assign({}, base.chart, { type: 'bar', height: 240 }),
                            series: [{ name: @json(__('merchandising.kpi_visits')), data: Object.values(days) }],
                            xaxis: { categories: Object.keys(days), labels: { rotate: -45, hideOverlappingLabels: true } },
                            yaxis: { labels: { formatter: function (v) { return Math.round(v); } } },
                            colors: ['#487FFF'], plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                        })).render();
                    }
                })();
            </script>
        @endpush
    @endif
</x-app-layout>
