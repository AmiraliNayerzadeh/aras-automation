@php($max = max($counts ?: [0]))
@forelse ($counts as $code => $count)
    <div class="mr-bar">
        <div class="mr-bar__head"><span>{{ $names[$code] ?? $code }}</span><strong>{{ $count }}</strong></div>
        <div class="mr-bar__track"><div class="mr-bar__fill" style="width: {{ $max ? round($count / $max * 100) : 0 }}%; background: {{ $color }};"></div></div>
    </div>
@empty
    <p class="text-secondary-light mb-0">{{ __('merchandising.nothing_recorded') }}</p>
@endforelse
