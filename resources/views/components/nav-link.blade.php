@props(['active'])

@php
$classes = ($active ?? false) ? 'active-page' : '';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
