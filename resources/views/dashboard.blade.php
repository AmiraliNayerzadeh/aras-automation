<x-app-layout>
    <section class="dash-hero radius-16 mb-24">
        <div class="dash-hero__body">
            <div class="dash-hero__text">
                <p class="dash-hero__eyebrow mb-8">
                    <i class="{{ ['morning' => 'ri-sun-line', 'afternoon' => 'ri-sun-foggy-line', 'evening' => 'ri-sparkling-2-line', 'night' => 'ri-moon-clear-line'][$greeting] }}"></i>
                    {{ __('dashboard.greeting_'.$greeting) }}
                </p>
                <h1 class="dash-hero__title mb-8">{{ __('dashboard.welcome', ['name' => auth()->user()->name]) }}</h1>
                <p class="dash-hero__subtitle mb-0">{{ __('dashboard.subtitle') }}</p>

                <div class="dash-hero__chips">
                    <span class="dash-chip"><i class="ri-calendar-line"></i> {{ $todayLabel }}</span>
                    @if ($checkInToday)
                        <span class="dash-chip"><i class="ri-login-circle-line"></i> {{ __('dashboard.checked_in_at', ['time' => $checkInToday]) }}</span>
                    @endif
                    <span class="dash-chip"><i class="ri-flashlight-line"></i>
                        {{ $pendingCount > 0 ? trans_choice('dashboard.pending_actions', $pendingCount, ['count' => $pendingCount]) : __('dashboard.all_clear') }}
                    </span>
                </div>
            </div>
            <img src="{{ auth()->user()->avatar_url }}" alt="" class="dash-hero__avatar">
        </div>
    </section>

    @foreach ($birthdaysToday as $birthdayUser)
        <div class="alert alert-warning radius-12 alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="ri-cake-2-line text-xl"></i>
            {{ __('app.birthday_banner', ['name' => $birthdayUser->name]) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endforeach

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

    @if ($shortcuts)
        <section class="mb-24" aria-labelledby="dash-shortcuts-title">
            <h2 id="dash-shortcuts-title" class="dash-section-title">{{ __('dashboard.shortcuts_title') }}</h2>
            <div class="dash-shortcuts">
                @foreach ($shortcuts as $shortcut)
                    <a href="{{ $shortcut['url'] }}" class="dash-tile dash-tone-{{ $shortcut['tone'] }}">
                        <span class="dash-tile__icon"><i class="{{ $shortcut['icon'] }}"></i></span>
                        <span class="dash-tile__text">
                            <span class="dash-tile__label">{{ __('dashboard.shortcut_'.$shortcut['key']) }}</span>
                            <span class="dash-tile__hint">{{ __('dashboard.shortcut_'.$shortcut['key'].'_hint') }}</span>
                        </span>
                        @if ($shortcut['badge'])
                            <span class="dash-tile__badge">{{ $shortcut['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="row g-3 mb-24">
        <div class="{{ $recentNotifications->isNotEmpty() ? 'col-lg-7' : 'col-12' }}">
            <div class="card radius-12 h-100">
                <div class="card-header bg-base fw-semibold d-flex align-items-center gap-2">
                    <i class="ri-flashlight-line text-primary-600"></i> {{ __('app.my_actions') }}
                </div>
                <div class="card-body">
                    @forelse ($actionItems as $item)
                        <a href="{{ $item['url'] }}" class="d-flex justify-content-between align-items-center gap-2 text-decoration-none py-12 {{ $loop->last ? '' : 'border-bottom' }}">
                            <span class="text-primary-light">{{ $item['label'] }}</span>
                            <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-primary-600 bg-primary-50 rounded-pill">{{ $item['count'] }}</span>
                        </a>
                    @empty
                        <div class="dash-empty">
                            <i class="ri-checkbox-circle-line"></i>
                            <span>{{ __('app.no_action_items') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if ($recentNotifications->isNotEmpty())
            <div class="col-lg-5">
                <div class="card radius-12 h-100">
                    <div class="card-header bg-base fw-semibold d-flex align-items-center gap-2">
                        <i class="ri-notification-3-line text-primary-600"></i> {{ __('notifications.dashboard_recent_title') }}
                    </div>
                    <div class="card-body">
                        @foreach ($recentNotifications as $notification)
                            @php($presented = \App\Support\NotificationPresenter::present($notification))
                            @if ($presented)
                                <a href="{{ $presented['url'] }}"
                                    class="d-flex justify-content-between align-items-center gap-2 text-decoration-none py-12 {{ $loop->last ? '' : 'border-bottom' }}">
                                    <span class="text-primary-light d-flex align-items-center gap-2">
                                        <i class="{{ $presented['icon'] }} text-primary-600"></i>
                                        {{ $presented['text'] }}
                                    </span>
                                    <span class="text-secondary-light text-xs flex-shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('files._quick_access', ['quickAccess' => $quickAccess])

    @if ($stats)
        <div class="row row-cols-xxl-4 row-cols-sm-2 row-cols-1 g-3">
            @foreach ([
                ['app.stat_users', $stats['users'], 'bg-gradient-start-1', 'bg-cyan', 'ri-team-line'],
                ['app.stat_companies', $stats['companies'], 'bg-gradient-start-2', 'bg-purple', 'ri-building-line'],
                ['app.stat_departments', $stats['departments'], 'bg-gradient-start-3', 'bg-info', 'ri-git-branch-line'],
                ['app.stat_active_users', $stats['active_users'], 'bg-gradient-start-4', 'bg-success-main', 'ri-user-follow-line'],
            ] as [$label, $value, $background, $iconBackground, $icon])
                <div class="col">
                    <div class="card shadow-none border {{ $background }} h-100 radius-12">
                        <div class="card-body p-20">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <p class="fw-medium text-primary-light mb-1">{{ __($label) }}</p>
                                    <h6 class="mb-0">{{ number_format($value) }}</h6>
                                </div>
                                <div class="w-50-px h-50-px {{ $iconBackground }} rounded-circle d-flex justify-content-center align-items-center">
                                    <i class="{{ $icon }} text-white text-2xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @push('styles')
        <style>
            .dash-hero {
                position: relative; overflow: hidden; color: #fff;
                background: linear-gradient(135deg, #487FFF 0%, #6A5CFF 55%, #8E5CF7 100%);
                box-shadow: 0 12px 32px -16px rgba(72, 127, 255, .7);
            }
            .dash-hero::before, .dash-hero::after {
                content: ""; position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .08); pointer-events: none;
            }
            .dash-hero::before { width: 320px; height: 320px; inset-inline-end: -80px; top: -140px; }
            .dash-hero::after { width: 200px; height: 200px; inset-inline-start: 38%; bottom: -120px; }
            .dash-hero__body { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 28px 32px; }
            .dash-hero__eyebrow { display: flex; align-items: center; gap: 6px; font-size: 14px; color: rgba(255, 255, 255, .85); }
            /* The theme sets every h1 with !important. */
            .dash-hero__title { font-size: clamp(1.35rem, 1rem + 1.4vw, 1.9rem) !important; font-weight: 700; color: #fff; line-height: 1.35; }
            .dash-hero__subtitle { color: rgba(255, 255, 255, .85); font-size: 15px; }
            .dash-hero__chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
            .dash-chip {
                display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px;
                background: rgba(255, 255, 255, .16); backdrop-filter: blur(4px); font-size: 13px; color: #fff;
            }
            .dash-hero__avatar {
                flex: 0 0 auto; width: 88px; height: 88px; border-radius: 50%; object-fit: cover;
                border: 3px solid rgba(255, 255, 255, .7); box-shadow: 0 6px 18px rgba(0, 0, 0, .18);
            }
            .dash-section-title { font-size: 16px !important; font-weight: 600; color: var(--text-primary-light); margin-bottom: 12px; }
            .dash-shortcuts { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px; }
            .dash-tile {
                --tone: 72, 127, 255;
                position: relative; display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px;
                background: var(--white); border: 1px solid var(--border-color); text-decoration: none; color: var(--text-primary-light);
                transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
            }
            .dash-tile:hover { transform: translateY(-2px); border-color: rgba(var(--tone), .45); box-shadow: 0 10px 24px -14px rgba(var(--tone), .8); color: var(--text-primary-light); }
            .dash-tile:focus-visible { outline: 2px solid rgb(var(--tone)); outline-offset: 2px; }
            .dash-tile__icon {
                flex: 0 0 auto; width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;
                background: rgba(var(--tone), .12); color: rgb(var(--tone)); font-size: 22px;
            }
            .dash-tile__text { display: flex; flex-direction: column; min-width: 0; }
            .dash-tile__label { font-weight: 600; font-size: 14px; }
            .dash-tile__hint { font-size: 12px; color: var(--text-secondary-light); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .dash-tile__badge {
                margin-inline-start: auto; min-width: 24px; height: 24px; padding: 0 7px; border-radius: 999px; display: inline-flex;
                align-items: center; justify-content: center; font-size: 12px; font-weight: 700; background: rgb(var(--tone)); color: #fff;
            }
            .dash-tone-primary { --tone: 72, 127, 255; }
            .dash-tone-success { --tone: 34, 167, 94; }
            .dash-tone-warning { --tone: 240, 145, 30; }
            .dash-tone-danger { --tone: 239, 68, 68; }
            .dash-tone-info { --tone: 14, 145, 233; }
            .dash-tone-purple { --tone: 132, 90, 223; }
            .dash-tone-cyan { --tone: 0, 168, 181; }
            .dash-empty { display: flex; align-items: center; gap: 8px; color: var(--text-secondary-light); }
            .dash-empty i { font-size: 20px; color: #22A75E; }
            @media (max-width: 575.98px) {
                .dash-hero__body { padding: 22px 20px; }
                .dash-hero__avatar { width: 60px; height: 60px; align-self: flex-start; }
                .dash-shortcuts { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
                .dash-tile { flex-direction: column; align-items: flex-start; }
                .dash-tile__badge { position: absolute; top: 10px; inset-inline-end: 10px; }
                .dash-tile__hint { white-space: normal; }
            }
            @media (prefers-reduced-motion: reduce) {
                .dash-tile { transition: none; }
                .dash-tile:hover { transform: none; }
            }
        </style>
    @endpush
</x-app-layout>
