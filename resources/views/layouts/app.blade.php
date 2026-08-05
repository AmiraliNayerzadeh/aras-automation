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
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
            <div class="container-fluid">
                <button class="btn btn-outline-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                    <i class="bi bi-list"></i>
                </button>

                <a class="navbar-brand" href="{{ route('dashboard') }}">{{ config('app.name', 'Aras Automation') }}</a>

                <div class="d-flex align-items-center order-lg-last gap-2">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button">
                                {{ strtoupper(app()->getLocale()) }}
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @foreach (['en' => 'English', 'hy' => 'Հայերեն', 'fa' => 'فارسی'] as $code => $label)
                                <x-dropdown-link href="{{ route('locale.switch', $code) }}">{{ $label }}</x-dropdown-link>
                            @endforeach
                        </x-slot>
                    </x-dropdown>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button">
                                {{ Auth::user()->name }}
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('app.nav_profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </nav>

        <div class="d-flex">
            <div class="offcanvas-lg offcanvas-start bg-body-tertiary border-end" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel" style="width: 15rem;">
                <div class="offcanvas-header d-lg-none">
                    <h5 class="offcanvas-title" id="sidebarLabel">{{ config('app.name', 'Aras Automation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
                </div>
                <div class="offcanvas-body p-0">
                    @include('layouts.navigation')
                </div>
            </div>

            <main class="flex-fill p-3 p-lg-4" style="min-width: 0;">
                @isset($header)
                    <div class="mb-4">
                        {{ $header }}
                    </div>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
