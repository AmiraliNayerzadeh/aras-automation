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
        <aside class="sidebar">
            <button type="button" class="sidebar-close-btn">
                <i class="ri-close-line"></i>
            </button>
            <div>
                <a href="{{ route('dashboard') }}" class="sidebar-logo">
                    <img src="{{ asset('assets/wowdash/images/logo.png') }}" alt="{{ config('app.name') }}" class="light-logo">
                    <img src="{{ asset('assets/wowdash/images/logo-light.png') }}" alt="{{ config('app.name') }}" class="dark-logo">
                    <img src="{{ asset('assets/wowdash/images/logo-icon.png') }}" alt="{{ config('app.name') }}" class="logo-icon">
                </a>
            </div>
            <div class="sidebar-menu-area">
                @include('layouts.navigation')
            </div>
        </aside>

        <main class="dashboard-main">
            <div class="navbar-header">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <button type="button" class="sidebar-toggle">
                                <i class="ri-menu-line icon text-2xl non-active"></i>
                                <i class="ri-arrow-right-line icon text-2xl active"></i>
                            </button>
                            <button type="button" class="sidebar-mobile-toggle">
                                <i class="ri-menu-line icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-auto">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div class="dropdown">
                                <button class="w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center fw-semibold text-sm" type="button" data-bs-toggle="dropdown">
                                    {{ strtoupper(app()->getLocale()) }}
                                </button>
                                <div class="dropdown-menu to-top dropdown-menu-sm">
                                    @foreach (['en' => 'English', 'hy' => 'Հայերեն', 'fa' => 'فارسی'] as $code => $label)
                                        <a href="{{ route('locale.switch', $code) }}" class="dropdown-item px-16 py-8 {{ app()->getLocale() === $code ? 'bg-primary-50 text-primary-600' : '' }}">{{ $label }}</a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="dropdown">
                                <button class="d-flex justify-content-center align-items-center rounded-circle" type="button" data-bs-toggle="dropdown">
                                    <img src="{{ asset('assets/wowdash/images/user.png') }}" alt="{{ Auth::user()->name }}" class="w-40-px h-40-px object-fit-cover rounded-circle">
                                </button>
                                <div class="dropdown-menu to-top dropdown-menu-sm">
                                    <div class="py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            <h6 class="text-lg text-primary-light fw-semibold mb-2">{{ Auth::user()->name }}</h6>
                                            <span class="text-secondary-light fw-medium text-sm">{{ Auth::user()->email }}</span>
                                        </div>
                                    </div>
                                    <ul class="to-top-list">
                                        <li>
                                            <a class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-primary d-flex align-items-center gap-3" href="{{ route('profile.edit') }}">
                                                <i class="ri-user-line icon text-xl"></i> {{ __('app.nav_profile') }}
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3 w-100 border-0 bg-transparent text-start">
                                                    <i class="ri-logout-box-line icon text-xl"></i> {{ __('app.logout') }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-main-body">
                @isset($header)
                    <div class="mb-24">
                        {{ $header }}
                    </div>
                @endisset

                {{ $slot }}
            </div>
        </main>

        <script src="{{ asset('assets/wowdash/js/lib/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('assets/wowdash/js/lib/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/wowdash/js/app.js') }}"></script>
    </body>
</html>
