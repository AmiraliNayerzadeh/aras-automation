<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">
            {{ __('app.nav_dashboard') }}
        </h2>
    </x-slot>

    @include('files._quick_access', ['quickAccess' => $quickAccess])

    @include('admin.payment-reminders._status')

    @if ($dueReminders->isNotEmpty())
        <div class="card radius-12 mb-24">
            <div class="card-header bg-base fw-semibold d-flex justify-content-between align-items-center gap-2">
                <span class="d-flex align-items-center gap-2">
                    <i class="ri-bank-card-line text-danger-600"></i> {{ __('payments.dashboard_title') }}
                </span>
                <a href="{{ route('admin.payment-reminders.index') }}" class="text-sm text-primary-600 fw-normal">{{ __('app.view_details') }}</a>
            </div>
            <div class="card-body py-8">
                @foreach ($dueReminders as $reminder)
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-12 {{ $loop->last ? '' : 'border-bottom' }}">
                        <div>
                            <div class="text-primary-light fw-medium">{{ $reminder->title }}</div>
                            <div class="text-secondary-light text-sm">
                                {{ $reminder->next_due_date->toDateString() }}
                                @if ($reminder->formattedAmount())
                                    · {{ $reminder->formattedAmount() }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @include('admin.payment-reminders._badge')
                            @include('admin.payment-reminders._pay_button')
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @include('admin.payment-reminders._pay_modal')
    @endif

    @if ($recentNotifications->isNotEmpty())
        <div class="card radius-12 mb-24">
            <div class="card-header bg-base fw-semibold d-flex align-items-center gap-2">
                <i class="ri-notification-3-line text-primary-600"></i> {{ __('notifications.dashboard_recent_title') }}
            </div>
            <div class="card-body">
                @foreach ($recentNotifications as $notification)
                    @php($presented = \App\Support\NotificationPresenter::present($notification))
                    @if ($presented)
                        <a href="{{ $presented['url'] }}"
                            class="d-flex justify-content-between align-items-center text-decoration-none py-12 border-bottom">
                            <span class="text-primary-light d-flex align-items-center gap-2">
                                <i class="{{ $presented['icon'] }} text-primary-600"></i>
                                {{ $presented['text'] }}
                            </span>
                            <span class="text-secondary-light text-xs flex-shrink-0 ms-8">{{ $notification->created_at->diffForHumans() }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @foreach ($birthdaysToday as $birthdayUser)
        <div class="alert alert-warning radius-8 alert-dismissible fade show" role="alert">
            {{ __('app.birthday_banner', ['name' => $birthdayUser->name]) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endforeach

    <div class="row row-cols-xxl-4 row-cols-sm-2 row-cols-1 g-3 mb-24">
        <div class="col">
            <div class="card shadow-none border bg-gradient-start-1 h-100 radius-12">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">{{ __('app.stat_users') }}</p>
                            <h6 class="mb-0">{{ number_format($stats['users']) }}</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                            <i class="ri-team-line text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-none border bg-gradient-start-2 h-100 radius-12">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">{{ __('app.stat_companies') }}</p>
                            <h6 class="mb-0">{{ number_format($stats['companies']) }}</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                            <i class="ri-building-line text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-none border bg-gradient-start-3 h-100 radius-12">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">{{ __('app.stat_departments') }}</p>
                            <h6 class="mb-0">{{ number_format($stats['departments']) }}</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-info rounded-circle d-flex justify-content-center align-items-center">
                            <i class="ri-git-branch-line text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card shadow-none border bg-gradient-start-4 h-100 radius-12">
                <div class="card-body p-20">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <p class="fw-medium text-primary-light mb-1">{{ __('app.stat_active_users') }}</p>
                            <h6 class="mb-0">{{ number_format($stats['active_users']) }}</h6>
                        </div>
                        <div class="w-50-px h-50-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                            <i class="ri-user-follow-line text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card radius-12">
        <div class="card-header bg-base fw-semibold d-flex align-items-center gap-2">
            <i class="ri-flashlight-line text-primary-600"></i> {{ __('app.my_actions') }}
        </div>
        <div class="card-body">
            @forelse ($actionItems as $item)
                <a href="{{ $item['url'] }}" class="d-flex justify-content-between align-items-center text-decoration-none py-12 border-bottom">
                    <span class="text-primary-light">{{ $item['label'] }}</span>
                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-primary-600 bg-primary-50 rounded-pill">{{ $item['count'] }}</span>
                </a>
            @empty
                <p class="text-secondary-light mb-0">{{ __('app.no_action_items') }}</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
