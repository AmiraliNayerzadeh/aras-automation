@php
    $__themeColorKey = app(\App\Services\SettingsService::class)->get('primary_color', config('theme.default_color'));
    $__themePreset = config("theme.colors.{$__themeColorKey}");
@endphp
@if ($__themePreset && $__themeColorKey !== config('theme.default_color'))
    <style>
        :root {
            @foreach ($__themePreset['shades'] as $__shade => $__hex)
                --primary-{{ $__shade }}: {{ $__hex }};
            @endforeach
            --primary-light: {{ $__themePreset['light'] }};
            --primary-light-white: {{ $__themePreset['light_white'] }};
        }
    </style>
@endif
