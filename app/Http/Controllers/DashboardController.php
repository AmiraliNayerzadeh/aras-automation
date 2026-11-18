<?php

namespace App\Http\Controllers;

use App\Models\FileManager\FileFavorite;
use App\Models\Finance\PaymentReminder;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\User;
use App\Services\SettingsService;
use App\Support\DashboardShortcuts;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use IntlDateFormatter;

class DashboardController extends Controller
{
    public function index(DashboardWidgetRegistry $widgets, DashboardShortcuts $shortcuts, SettingsService $settings, Request $request)
    {
        $user = $request->user();

        $birthdaysToday = User::query()
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->get();

        // Company-wide figures are for those who manage people or structure.
        $stats = $user->canAny(['users.view', 'organization.view']) ? [
            'users' => User::count(),
            'companies' => Company::count(),
            'departments' => Department::count(),
            'active_users' => User::where('status', 'active')->count(),
        ] : null;

        $quickAccess = FileFavorite::where('user_id', $user->id)
            ->with('favoritable')
            ->latest()
            ->get()
            ->pluck('favoritable')
            ->filter()
            ->filter(fn ($item) => $item->isVisibleTo($user))
            ->values();

        $recentNotifications = $user->notifications()
            ->where('created_at', '>=', now()->subMinutes(30))
            ->latest()
            ->get();

        $actionItems = $widgets->resolve();
        $now = now($this->timezone($settings));

        return view('dashboard', [
            'greeting' => $this->greeting($now),
            'todayLabel' => $this->todayLabel($now),
            'checkInToday' => $this->checkInToday($user, $now),
            'pendingCount' => array_sum(array_column($actionItems, 'count')),
            'shortcuts' => $shortcuts->for($user),
            'birthdaysToday' => $birthdaysToday,
            'actionItems' => $actionItems,
            'stats' => $stats,
            'quickAccess' => $quickAccess,
            'recentNotifications' => $recentNotifications,
            'dueReminders' => $user->can('payment-reminders.manage') ? PaymentReminder::needingAttention() : collect(),
        ]);
    }

    /**
     * The app runs on UTC; the greeting and date follow the company's own
     * timezone from Settings, so nobody is told "good night" at noon.
     */
    protected function timezone(SettingsService $settings): string
    {
        $timezone = (string) $settings->get('timezone', config('app.timezone'));

        return in_array($timezone, timezone_identifiers_list(), true) ? $timezone : config('app.timezone');
    }

    protected function greeting(Carbon $now): string
    {
        return match (true) {
            $now->hour >= 5 && $now->hour < 12 => 'morning',
            $now->hour >= 12 && $now->hour < 17 => 'afternoon',
            $now->hour >= 17 && $now->hour < 21 => 'evening',
            default => 'night',
        };
    }

    /**
     * Persian users read the date in the Solar Hijri calendar.
     */
    protected function todayLabel(Carbon $now): string
    {
        if (app()->getLocale() === 'fa') {
            $formatter = new IntlDateFormatter('fa_IR@calendar=persian', IntlDateFormatter::FULL, IntlDateFormatter::NONE, $now->timezone, IntlDateFormatter::TRADITIONAL, 'EEEE d MMMM y');

            return (string) $formatter->format($now->getTimestamp());
        }

        return $now->translatedFormat('l, j F Y');
    }

    /**
     * Device times are stored as the terminal's wall-clock time, so "today" is
     * matched on the company's local date.
     */
    protected function checkInToday(User $user, Carbon $now): ?string
    {
        return FaceDeviceEvent::where('user_id', $user->id)
            ->whereDate('event_time', $now->toDateString())
            ->orderBy('event_time')
            ->first()
            ?->event_time->format('H:i');
    }
}
