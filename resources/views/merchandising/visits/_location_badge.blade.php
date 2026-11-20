@php
    [$tone, $icon] = match ($visit->location_status) {
        \App\Services\Merchandising\VisitLocation::VERIFIED => ['success', 'ri-map-pin-2-fill'],
        \App\Services\Merchandising\VisitLocation::STORE_UNMAPPED => ['warning', 'ri-map-pin-line'],
        default => ['danger', 'ri-map-pin-off-line'],
    };
    $label = __('merchandising.location_'.($visit->location_status ?? 'no_location'));
@endphp
<span class="badge text-xs fw-semibold px-8 py-4 radius-4 text-{{ $tone }}-600 bg-{{ $tone }}-100 text-nowrap"
    @if ($visit->distance_m !== null) title="{{ __('merchandising.location_distance', ['distance' => $visit->distance_m]) }}" @endif>
    <i class="{{ $icon }}"></i> {{ $label }}
</span>
