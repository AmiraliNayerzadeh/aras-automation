<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('merchandising.title_visit') }}</h2>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('merchandising.visits.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm"><i class="ri-arrow-go-back-line"></i> {{ __('merchandising.nav_visits') }}</a>
                @if ($visit->folder_id)
                    <a href="{{ route('files.index', ['folder' => $visit->folder_id]) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm"><i class="ri-folder-3-line"></i> {{ __('merchandising.open_folder') }}</a>
                @endif
                @can('merchandising.view_all')
                    <form method="POST" action="{{ route('merchandising.visits.destroy', $visit) }}" onsubmit="return confirm(@js(__('merchandising.confirm_delete_visit')));">
                        @csrf @method('delete')
                        <button class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm"><i class="ri-delete-bin-line"></i> {{ __('merchandising.delete_visit') }}</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'visit-saved')
        <div class="alert alert-success radius-12 d-flex align-items-center gap-2"><i class="ri-checkbox-circle-line text-xl"></i> {{ __('merchandising.flash_visit_saved') }}</div>
    @endif

    @php
        $stockTone = ['in_stock' => 'success', 'low' => 'warning', 'out_of_stock' => 'danger', 'not_listed' => 'danger'];
        $byKind = $visit->media->groupBy('kind');
    @endphp

    <div class="row g-3 mb-24">
        <div class="col-lg-8">
            <div class="card radius-12 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="w-48-px h-48-px radius-12 bg-primary-50 text-primary-600 d-flex align-items-center justify-content-center text-2xl flex-shrink-0"><i class="ri-store-2-line"></i></span>
                        <div class="min-w-0">
                            <h3 class="h5 fw-semibold mb-4">
                                {{ $visit->store?->name ?? __('merchandising.unlisted_store') }}
                                @if ($visit->store && ! $visit->store->is_verified)
                                    <span class="badge text-xs fw-semibold px-8 py-4 radius-4 text-warning-600 bg-warning-100 align-middle">{{ __('merchandising.typed_store_badge') }}</span>
                                @endif
                            </h3>
                            <div class="text-secondary-light text-sm">{{ collect([$visit->store?->codes(), $visit->store?->region, $visit->store?->address])->filter()->implode(' · ') }}</div>
                        </div>
                    </div>
                    <div class="row g-3 mt-8 text-sm">
                        <div class="col-sm-4"><div class="text-secondary-light">{{ __('merchandising.field_visited_at') }}</div><div class="fw-semibold">{{ $visit->visited_at->format('Y-m-d H:i') }}</div></div>
                        <div class="col-sm-4"><div class="text-secondary-light">{{ __('merchandising.field_merchandiser') }}</div><div class="fw-semibold">{{ $visit->user?->name }}</div></div>
                        <div class="col-sm-4"><div class="text-secondary-light">{{ __('merchandising.field_divisions') }}</div><div class="fw-semibold">{{ collect($visit->divisions)->map(fn ($d) => __('merchandising.division_'.$d))->implode(', ') ?: '—' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card radius-12 h-100">
                <div class="card-body text-sm">
                    <div class="text-secondary-light mb-4">{{ __('merchandising.section_actions') }}</div>
                    <div class="d-flex flex-wrap gap-1 mb-12">
                        @forelse ((array) $visit->actions as $code)
                            <span class="badge text-xs fw-medium px-10 py-6 radius-4 text-primary-600 bg-primary-50">{{ $actions[$code] ?? $code }}</span>
                        @empty
                            <span class="text-secondary-light">{{ __('merchandising.none') }}</span>
                        @endforelse
                    </div>
                    <div class="text-secondary-light mb-4">{{ __('merchandising.field_feedback') }}</div>
                    <div class="d-flex flex-wrap gap-1 mb-12">
                        @forelse ((array) $visit->store_feedback as $code)
                            <span class="badge text-xs fw-medium px-10 py-6 radius-4 text-info-600 bg-info-100">{{ $feedback[$code] ?? $code }}</span>
                        @empty
                            <span class="text-secondary-light">{{ __('merchandising.none') }}</span>
                        @endforelse
                    </div>
                    @if ($visit->note)
                        <div class="text-secondary-light mb-4">{{ __('merchandising.field_note') }}</div>
                        <div>{{ $visit->note }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @foreach (['before', 'after', 'extra'] as $kind)
        @if ($byKind->has($kind))
            <div class="card radius-12 mb-24">
                <div class="card-header bg-base fw-semibold">{{ __('merchandising.photos_'.$kind) }} <span class="text-secondary-light fw-normal">({{ $byKind[$kind]->count() }})</span></div>
                <div class="card-body">
                    <div class="mvs-gallery">
                        @foreach ($byKind[$kind] as $media)
                            @php($url = $media->fileEntry ? asset('storage/'.$media->fileEntry->file_path) : null)
                            @if ($url && $media->isVideo())
                                <video src="{{ $url }}" controls preload="metadata" class="mvs-media"></video>
                            @elseif ($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="mvs-media"><img src="{{ $url }}" alt="{{ $media->original_name }}" loading="lazy"></a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <div class="card radius-12">
        <div class="card-header bg-base fw-semibold">{{ __('merchandising.products_selected') }} <span class="text-secondary-light fw-normal">({{ $visit->items->count() }})</span></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle text-sm">
                <thead>
                    <tr>
                        <th>{{ __('merchandising.section_products') }}</th>
                        <th>{{ __('merchandising.field_stock') }}</th>
                        <th>{{ __('merchandising.field_shelf') }}</th>
                        <th>{{ __('merchandising.field_price_tag') }}</th>
                        <th>{{ __('merchandising.field_issues') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visit->items as $item)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $item->displayName() }}
                                    @if ($item->needs_order) <i class="ri-shopping-cart-2-line text-warning-600" title="{{ __('merchandising.order_pick') }}"></i> @endif
                                </div>
                                <div class="text-secondary-light text-xs">{{ collect([$item->product?->sku, $item->product?->line])->filter()->implode(' · ') ?: __('merchandising.product_custom_badge') }}</div>
                            </td>
                            <td>
                                @if ($item->stock_status)
                                    <span class="badge text-xs fw-semibold px-10 py-4 radius-4 text-{{ $stockTone[$item->stock_status] }}-600 bg-{{ $stockTone[$item->stock_status] }}-100">{{ __('merchandising.stock_'.$item->stock_status) }}</span>
                                @else — @endif
                            </td>
                            <td>{{ $item->shelf_position ? __('merchandising.shelf_'.$item->shelf_position) : '—' }}</td>
                            <td>{{ $item->price_tag ? __('merchandising.price_tag_'.$item->price_tag) : '—' }}</td>
                            <td>{{ collect($item->issues)->map(fn ($c) => $issues[$c] ?? $c)->implode(', ') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary-light py-4">{{ __('merchandising.products_none_selected') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('styles')
        <style>
            .mvs-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
            .mvs-media { display: block; aspect-ratio: 4 / 3; border-radius: 10px; overflow: hidden; background: var(--neutral-100); width: 100%; }
            .mvs-media img { width: 100%; height: 100%; object-fit: cover; transition: transform .2s; }
            a.mvs-media:hover img { transform: scale(1.03); }
            .min-w-0 { min-width: 0; }
        </style>
    @endpush
</x-app-layout>
