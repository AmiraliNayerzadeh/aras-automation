<x-guest-layout>
    <div class="mb-32">
        <h4 class="mb-12">{{ __('app.forgot_password_heading') }}</h4>
        <p class="mb-0 text-secondary-light text-lg">{{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y">
                <i class="ri-mail-line"></i>
            </span>
            <x-text-input id="email" class="h-56-px bg-neutral-50 radius-12" type="email" name="email" :value="old('email')" placeholder="{{ __('app.field_email') }}" required autofocus />
        </div>
        <x-input-error :messages="$errors->get('email')" class="mb-32" />

        <x-primary-button class="text-sm w-100">
            {{ __('Email Password Reset Link') }}
        </x-primary-button>
    </form>
</x-guest-layout>
