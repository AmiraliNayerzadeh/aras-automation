@php($item = $item ?? null)

@if ($item && $item->relationLoaded('labels') && $item->labels->isNotEmpty())
    <div class="d-flex flex-wrap gap-4 {{ $class ?? '' }}">
        @foreach ($item->labels as $label)
            <span class="badge text-xs fw-semibold px-8 py-2 radius-4 text-{{ $label->color }}-600 bg-{{ $label->color }}-100">
                {{ $label->label[app()->getLocale()] ?? $label->code }}
            </span>
        @endforeach
    </div>
@endif
