<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $htmlDir ?? 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts

        @if (($htmlDir ?? 'ltr') === 'rtl')
            @vite(['resources/css/app-rtl.css', 'resources/js/app.js'])
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body>
        <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center bg-body-tertiary py-4">
            <div>
                <a href="/">
                    <x-application-logo style="width: 4rem; height: 4rem;" class="text-secondary" />
                </a>
            </div>

            <div class="w-100 mt-3 px-4 py-4 bg-body shadow-sm rounded-3" style="max-width: 26rem;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
