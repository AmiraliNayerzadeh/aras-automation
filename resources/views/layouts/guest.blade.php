<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $htmlDir ?? 'ltr' }}" data-theme="light">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Aras Automation') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('assets/wowdash/images/favicon.png') }}" sizes="16x16">

        @fonts

        <link rel="stylesheet" href="{{ asset('assets/wowdash/css/remixicon.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/wowdash/css/lib/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/wowdash/css/style.css') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <section class="auth bg-base d-flex flex-wrap">
            <div class="auth-left d-lg-block d-none">
                <div class="d-flex align-items-center flex-column h-100 justify-content-center">
                    <img src="{{ asset('assets/wowdash/images/auth/auth-img.png') }}" alt="">
                </div>
            </div>
            <div class="auth-right py-32 px-24 d-flex flex-column justify-content-center">
                <div class="max-w-464-px mx-auto w-100">
                    <div class="mb-40 text-center">
                        <a href="{{ route('dashboard') }}" class="d-inline-block">
                            <img src="{{ asset('assets/wowdash/images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height: 3rem;">
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </section>

        <script src="{{ asset('assets/wowdash/js/lib/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('assets/wowdash/js/lib/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/wowdash/js/app.js') }}"></script>
    </body>
</html>
