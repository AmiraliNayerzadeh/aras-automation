<x-guest-layout>
    <div class="mb-32">
        <h4 class="mb-12">{{ __('app.register_heading') }}</h4>
        <p class="mb-0 text-secondary-light text-lg">{{ __('app.register_subheading') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y">
                <i class="ri-user-line"></i>
            </span>
            <x-text-input id="name" class="h-56-px bg-neutral-50 radius-12" type="text" name="name" :value="old('name')" placeholder="{{ __('app.field_name') }}" required autofocus autocomplete="name" />
        </div>
        <x-input-error :messages="$errors->get('name')" class="mb-16" />

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y">
                <i class="ri-mail-line"></i>
            </span>
            <x-text-input id="email" class="h-56-px bg-neutral-50 radius-12" type="email" name="email" :value="old('email')" placeholder="{{ __('app.field_email') }}" required autocomplete="username" />
        </div>
        <x-input-error :messages="$errors->get('email')" class="mb-16" />

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y">
                <i class="ri-lock-password-line"></i>
            </span>
            <x-text-input id="password" class="h-56-px bg-neutral-50 radius-12" type="password" name="password" placeholder="{{ __('app.field_password') }}" required autocomplete="new-password" />
        </div>
        <x-input-error :messages="$errors->get('password')" class="mb-16" />

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y">
                <i class="ri-lock-password-line"></i>
            </span>
            <x-text-input id="password_confirmation" class="h-56-px bg-neutral-50 radius-12" type="password" name="password_confirmation" placeholder="{{ __('Confirm Password') }}" required autocomplete="new-password" />
        </div>
        <x-input-error :messages="$errors->get('password_confirmation')" class="mb-32" />

        <x-primary-button class="text-sm w-100">
            {{ __('Register') }}
        </x-primary-button>

        <div class="mt-32 text-center text-sm">
            <p class="mb-0">{{ __('Already registered?') }} <a href="{{ route('login') }}" class="text-primary-600 fw-semibold">{{ __('Log in') }}</a></p>
        </div>
    </form>
</x-guest-layout>
