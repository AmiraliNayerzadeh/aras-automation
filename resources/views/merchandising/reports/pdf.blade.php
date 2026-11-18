<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('merchandising.title_reports') }}</title>
    <style>
        @page { margin: 24px 28px 40px; }
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 10px; color: #1F2937; }
        h1 { font-size: 20px; margin: 0; color: #111827; }
        h2 { font-size: 13px; margin: 18px 0 8px; color: #111827; border-bottom: 2px solid #487FFF; padding-bottom: 4px; }
        h3 { font-size: 11px; margin: 0 0 6px; color: #374151; }
        .muted { color: #6B7280; }
        .header { border-bottom: 1px solid #E5E7EB; padding-bottom: 10px; margin-bottom: 12px; }
        .brand { color: #487FFF; font-weight: bold; font-size: 11px; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; }
        .kpis td { width: 16.66%; padding: 4px; }
        .kpi { border: 1px solid #E5E7EB; border-radius: 6px; padding: 8px; text-align: center; }
        .kpi .value { font-size: 16px; font-weight: bold; color: #111827; }
        .kpi .label { font-size: 8.5px; color: #6B7280; }
        .grid td { width: 50%; vertical-align: top; padding: 4px 8px 4px 0; }
        .box { border: 1px solid #E5E7EB; border-radius: 6px; padding: 10px; }
        .bar-row td { padding: 2px 0; font-size: 9px; }
        .bar-track { background: #F3F4F6; height: 7px; border-radius: 4px; }
        .bar-fill { height: 7px; border-radius: 4px; }
        .data th { background: #487FFF; color: #fff; font-size: 9px; text-align: left; padding: 5px 6px; }
        .data td { border-bottom: 1px solid #E5E7EB; padding: 5px 6px; font-size: 9px; vertical-align: top; }
        .data tr:nth-child(even) td { background: #F9FAFB; }
        .right, .data th.right, .data td.right { text-align: right; }
        .pill { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8.5px; }
        .s-in_stock { background: #DDF3E4; color: #166534; } .s-low { background: #FDEBD3; color: #9A5B0B; }
        .s-out_of_stock, .s-not_listed { background: #FDE2E2; color: #991B1B; }
        .visit { page-break-inside: avoid; border: 1px solid #E5E7EB; border-radius: 8px; padding: 10px; margin-bottom: 10px; }
        .visit-head td { vertical-align: top; }
        .photos td { width: 25%; padding: 3px; vertical-align: top; }
        .photos img { width: 100%; border-radius: 4px; }
        .photo-label { font-size: 8px; color: #6B7280; text-transform: uppercase; }
        .tags span { display: inline-block; background: #EEF4FF; color: #2E5BDB; padding: 1px 6px; border-radius: 8px; margin: 0 3px 3px 0; font-size: 8.5px; }
        .footer { position: fixed; bottom: -26px; left: 0; right: 0; font-size: 8px; color: #9CA3AF; text-align: center; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="footer">Aras Food · Merchandising report · {{ $report->periodLabel() }}</div>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="brand">ARAS FOOD</div>
                    <h1>{{ __('merchandising.title_reports') }}</h1>
                    <div class="muted">{{ $report->periodLabel() }} · {{ $divisionLabel }}</div>
                </td>
                <td class="right muted">{{ __('merchandising.pdf_generated') }}: {{ now()->format('j M Y, H:i') }}</td>
            </tr>
        </table>
    </div>

    @if ($report->visits->isEmpty())
        <p class="muted">{{ __('merchandising.no_data') }}</p>
    @else
        <table class="kpis">
            <tr>
                @foreach ([
                    ['kpi_visits', number_format($kpis['visits'])],
                    ['kpi_stores', number_format($kpis['stores'])],
                    ['kpi_products', number_format($kpis['products_checked'])],
                    ['kpi_out_rate', $kpis['out_of_stock_rate'] !== null ? $kpis['out_of_stock_rate'].'%' : '—'],
                    ['kpi_price_ok', $kpis['price_tag_ok_rate'] !== null ? $kpis['price_tag_ok_rate'].'%' : '—'],
                    ['kpi_orders', number_format($kpis['orders_needed'])],
                ] as [$label, $value])
                    <td><div class="kpi"><div class="value">{{ $value }}</div><div class="label">{{ __('merchandising.'.$label) }}</div></div></td>
                @endforeach
            </tr>
        </table>

        @php
            $bars = function (array $counts, array $names, string|array $color) {
                $max = max($counts ?: [0]);
                return compact('counts', 'names', 'color', 'max');
            };
            $sections = [
                [__('merchandising.chart_stock'), $bars($stock, $labels['stock'], ['in_stock' => '#22A75E', 'low' => '#F0911E', 'out_of_stock' => '#EF4444', 'not_listed' => '#9CA3AF'])],
                [__('merchandising.chart_shelf'), $bars($shelf, $labels['shelf'], '#487FFF')],
                [__('merchandising.chart_price_tags'), $bars($priceTags, $labels['price_tag'], ['correct' => '#22A75E', 'missing' => '#EF4444', 'wrong' => '#F0911E'])],
                [__('merchandising.chart_issues'), $bars($issueCounts, $labels['issues'], '#EF4444')],
                [__('merchandising.chart_actions'), $bars($actionCounts, $labels['actions'], '#487FFF')],
                [__('merchandising.chart_feedback'), $bars($feedbackCounts, $labels['feedback'], '#0E91E9')],
            ];
        @endphp

        <h2>Overview</h2>
        <table class="grid">
            @foreach (array_chunk($sections, 2) as $pair)
                <tr>
                    @foreach ($pair as [$title, $data])
                        <td>
                            <div class="box">
                                <h3>{{ $title }}</h3>
                                @if (! array_filter($data['counts']))
                                    <div class="muted">{{ __('merchandising.nothing_recorded') }}</div>
                                @else
                                    <table>
                                        @foreach ($data['counts'] as $code => $count)
                                            <tr class="bar-row">
                                                <td style="width: 42%;">{{ $data['names'][$code] ?? $code }}</td>
                                                <td style="width: 46%;"><div class="bar-track"><div class="bar-fill" style="width: {{ $data['max'] ? round($count / $data['max'] * 100) : 0 }}%; background: {{ is_array($data['color']) ? ($data['color'][$code] ?? '#487FFF') : $data['color'] }};"></div></div></td>
                                                <td class="right" style="width: 12%;"><strong>{{ $count }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>

        @if ($topOut->isNotEmpty())
            <h2>{{ __('merchandising.table_top_out') }}</h2>
            <table class="data">
                <tr><th>{{ __('merchandising.col_product') }}</th><th>{{ __('merchandising.col_line') }}</th><th class="right">{{ __('merchandising.col_times') }}</th><th class="right">{{ __('merchandising.col_stores') }}</th></tr>
                @foreach ($topOut as $row)
                    <tr><td>{{ $row['name'] }}</td><td>{{ $row['line'] }}</td><td class="right">{{ $row['count'] }}</td><td class="right">{{ $row['stores'] }}</td></tr>
                @endforeach
            </table>
        @endif

        <h2>{{ __('merchandising.table_by_merchandiser') }}</h2>
        <table class="data">
            <tr><th>{{ __('merchandising.field_merchandiser') }}</th><th class="right">{{ __('merchandising.kpi_visits') }}</th><th class="right">{{ __('merchandising.col_stores') }}</th><th class="right">{{ __('merchandising.kpi_products') }}</th><th class="right">{{ __('merchandising.kpi_out_of_stock') }}</th><th class="right">{{ __('merchandising.kpi_photos') }}</th></tr>
            @foreach ($byMerchandiser as $row)
                <tr><td>{{ $row['name'] }}</td><td class="right">{{ $row['visits'] }}</td><td class="right">{{ $row['stores'] }}</td><td class="right">{{ $row['products'] }}</td><td class="right">{{ $row['out_of_stock'] }}</td><td class="right">{{ $row['photos'] }}</td></tr>
            @endforeach
        </table>

        <div class="page-break"></div>
        <h2>{{ __('merchandising.pdf_visit_details') }}</h2>
        @if ($report->visits->count() > $visitLimit)
            <p class="muted">{{ __('merchandising.pdf_limit_note', ['limit' => $visitLimit, 'total' => $report->visits->count()]) }}</p>
        @endif

        @foreach ($report->visits->take($visitLimit) as $visit)
            @php
                $items = $report->itemsOf($visit);
                $before = $visit->media->where('kind', 'before')->reject->isVideo()->take(2);
                $after = $visit->media->where('kind', 'after')->reject->isVideo()->take(2);
                $shown = $withPhotos ? $before->count() + $after->count() : 0;
            @endphp
            <div class="visit">
                <table class="visit-head">
                    <tr>
                        <td>
                            <strong style="font-size: 11.5px;">{{ $visit->store?->name ?? __('merchandising.unlisted_store') }}</strong><br>
                            <span class="muted">{{ collect([$visit->store?->codes(), $visit->store?->region, $visit->store?->address])->filter()->implode(' · ') }}</span>
                        </td>
                        <td class="right" style="width: 32%;">
                            <strong>{{ $visit->visited_at->format('D, j M Y · H:i') }}</strong><br>
                            <span class="muted">{{ $visit->user?->name }}</span>
                        </td>
                    </tr>
                </table>

                @if ($visit->actions || $visit->store_feedback || $visit->note || $visit->needs_order)
                    <table style="margin-top: 6px;">
                        <tr>
                            <td class="tags" style="width: 50%; vertical-align: top;">
                                @foreach ((array) $visit->actions as $code)<span>{{ $labels['actions'][$code] ?? $code }}</span>@endforeach
                                @foreach ((array) $visit->store_feedback as $code)<span style="background: #E0F2FE; color: #0369A1;">{{ $labels['feedback'][$code] ?? $code }}</span>@endforeach
                                @if ($visit->needs_order)<span style="background: #FEF3C7; color: #92400E;">Order needed</span>@endif
                            </td>
                            <td style="vertical-align: top;">{{ $visit->note }}</td>
                        </tr>
                    </table>
                @endif

                @if ($items->isNotEmpty())
                    <table class="data" style="margin-top: 6px;">
                        <tr><th>{{ __('merchandising.col_product') }}</th><th>{{ __('merchandising.field_stock') }}</th><th>{{ __('merchandising.field_shelf') }}</th><th>{{ __('merchandising.field_price_tag') }}</th><th>{{ __('merchandising.field_issues') }}</th></tr>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->displayName() }}@if ($item->needs_order) <strong style="color: #B45309;">(order)</strong>@endif</td>
                                <td>@if ($item->stock_status)<span class="pill s-{{ $item->stock_status }}">{{ $labels['stock'][$item->stock_status] }}</span>@endif</td>
                                <td>{{ $item->shelf_position ? $labels['shelf'][$item->shelf_position] : '' }}</td>
                                <td>{{ $item->price_tag ? $labels['price_tag'][$item->price_tag] : '' }}</td>
                                <td>{{ collect($item->issues)->map(fn ($c) => $labels['issues'][$c] ?? $c)->implode(', ') }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                @if ($withPhotos && $shown)
                    <table class="photos" style="margin-top: 6px;">
                        <tr>
                            @foreach ([$before, $after] as $index => $set)
                                @foreach ($set as $media)
                                    @php($src = $thumbnails->dataUri($media->fileEntry?->file_path))
                                    <td>
                                        <div class="photo-label">{{ $index === 0 ? __('merchandising.pdf_before') : __('merchandising.pdf_after') }}</div>
                                        @if ($src)<img src="{{ $src }}" alt="">@endif
                                    </td>
                                @endforeach
                            @endforeach
                            @for ($i = $shown; $i < 4; $i++)<td></td>@endfor
                        </tr>
                    </table>
                @endif
                @php($rest = $visit->media->count() - $shown)
                @if ($rest > 0)
                    <div class="muted" style="margin-top: 4px;">{{ __('merchandising.pdf_more_media', ['count' => $rest]) }}</div>
                @endif
            </div>
        @endforeach
    @endif
</body>
</html>
