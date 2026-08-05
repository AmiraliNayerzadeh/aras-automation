@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success radius-8 py-11 px-16 mb-16']) }}>
        {{ $status }}
    </div>
@endif
