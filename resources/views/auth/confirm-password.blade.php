<x-guest-layout>
    <div class="mb-32">
        <h4 class="mb-12">{{ __('app.confirm_password_heading') }}</h4>
        <p class="mb-0 text-secondary-light text-lg">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y">
                <i class="ri-lock-password-line"></i>
            </span>
            <x-text-input id="password" class="h-56-px bg-neutral-50 radius-12" type="password" name="password" placeholder="{{ __('app.field_password') }}" required autocomplete="current-password" />
        </div>
        <x-input-error :messages="$errors->get('password')" class="mb-32" />

        <x-primary-button class="text-sm w-100">
            {{ __('Confirm') }}
        </x-primary-button>
    </form>
</x-guest-layout>
