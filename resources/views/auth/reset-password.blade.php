<x-guest-layout>
    <div class="mb-32">
        <h4 class="mb-12">{{ __('app.reset_password_heading') }}</h4>
        <p class="mb-0 text-secondary-light text-lg">{{ __('app.reset_password_subheading') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="icon-field mb-16">
            <span class="icon top-50 translate-middle-y">
                <i class="ri-mail-line"></i>
            </span>
            <x-text-input id="email" class="h-56-px bg-neutral-50 radius-12" type="email" name="email" :value="old('email', $request->email)" placeholder="{{ __('app.field_email') }}" required autofocus autocomplete="username" />
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
            {{ __('Reset Password') }}
        </x-primary-button>
    </form>
</x-guest-layout>
