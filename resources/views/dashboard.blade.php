<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">
            {{ __('app.nav_dashboard') }}
        </h2>
    </x-slot>

    @foreach ($birthdaysToday as $birthdayUser)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ __('app.birthday_banner', ['name' => $birthdayUser->name]) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endforeach

    <div class="card">
        <div class="card-header bg-body fw-medium">{{ __('app.my_actions') }}</div>
        <div class="card-body">
            @forelse ($actionItems as $item)
                <a href="{{ $item['url'] }}" class="d-flex justify-content-between align-items-center text-decoration-none py-2 border-bottom">
                    <span>{{ $item['label'] }}</span>
                    <span class="badge text-bg-primary rounded-pill">{{ $item['count'] }}</span>
                </a>
            @empty
                <p class="text-muted mb-0">{{ __('app.no_action_items') }}</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
