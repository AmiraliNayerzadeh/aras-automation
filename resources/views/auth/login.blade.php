<x-guest-layout>
    <div class="mb-32">
        <h4 class="mb-12">{{ __('app.login_heading') }}</h4>
        <p class="mb-0 text-secondary-light text-lg">{{ __('app.login_subheading') }}</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y">
                <i class="ri-mail-line"></i>
            </span>
            <x-text-input id="email" class="h-56-px bg-neutral-50 radius-12" type="email" name="email" :value="old('email')" placeholder="{{ __('app.field_email') }}" required autofocus autocomplete="username" />
        </div>
        <x-input-error :messages="$errors->get('email')" class="mb-16" />

        <div class="position-relative mb-16">
            <div class="icon-field">
                <span class="icon top-50 translate-middle-y">
                    <i class="ri-lock-password-line"></i>
                </span>
                <x-text-input id="password" class="h-56-px bg-neutral-50 radius-12" type="password" name="password" placeholder="{{ __('app.field_password') }}" required autocomplete="current-password" />
            </div>
        </div>
        <x-input-error :messages="$errors->get('password')" class="mb-16" />

        <div class="d-flex justify-content-between gap-2 mb-32">
            <div class="form-check style-check d-flex align-items-center">
                <input class="form-check-input border border-neutral-300" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label" for="remember_me">{{ __('Remember me') }}</label>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-primary-600 fw-medium">{{ __('Forgot your password?') }}</a>
            @endif
        </div>

        <x-primary-button class="text-sm w-100">
            {{ __('Log in') }}
        </x-primary-button>
    </form>
</x-guest-layout>
