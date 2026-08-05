@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'text-danger-600 text-sm mt-4']) }}>
        @foreach ((array) $messages as $message)
            <div>{{ $message }}</div>
        @endforeach
    </div>
@endif
