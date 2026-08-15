<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\Hr\LeaveRequest;
use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\User;
use App\Support\WorkScheduleResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AttendanceReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:face-device-events.view'),
        ];
    }

    public function index(Request $request, WorkScheduleResolver $resolver): View
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->input('date_from')) : now()->startOfMonth();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->input('date_to')) : now();

        $users = User::query()
            ->with('workShifts')
            ->where('status', UserStatus::Active->value)
            ->when($request->filled('user_id'), fn ($q) => $q->where('id', $request->input('user_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->input('department_id')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->orderBy('name')
            ->get();

        $userIds = $users->pluck('id');

        $eventsByUserDay = FaceDeviceEvent::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('event_time', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->orderBy('event_time')
            ->get()
            ->groupBy(fn (FaceDeviceEvent $e) => $e->user_id.'|'.$e->event_time->toDateString());

        $leavesByUser = LeaveRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('status', RequestStatus::Approved->value)
            ->where('from_date', '<=', $dateTo->toDateString())
            ->where('to_date', '>=', $dateFrom->toDateString())
            ->get()
            ->groupBy('user_id');

        $daily = $this->buildDailyEntries($users, $dateFrom, $dateTo, $eventsByUserDay, $leavesByUser, $resolver);

        $viewData = [
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
            'users' => User::where('status', UserStatus::Active->value)->orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
        ];

        if ($request->filled('user_id')) {
            $rows = $daily->sortBy('date')->values();

            return view('admin.attendance-report.index', $viewData + [
                'mode' => 'detail',
                'employee' => $users->first(),
                'rows' => $rows,
                'chartData' => [
                    'labels' => $rows->pluck('date')->all(),
                    'values' => $rows->map(fn ($r) => $r['worked_minutes'] !== null ? round($r['worked_minutes'] / 60, 2) : 0)->all(),
                ],
            ]);
        }

        $summary = $daily
            ->groupBy('user_id')
            ->map(function (Collection $rows) {
                $present = $rows->where('status', 'present');

                return [
                    'user' => $rows->first()['user'],
                    'present_days' => $present->count(),
                    'absent_days' => $rows->where('status', 'absent')->count(),
                    'on_leave_days' => $rows->where('status', 'on_leave')->count(),
                    'remote_days' => $rows->where('status', 'remote')->count(),
                    'incomplete_days' => $rows->where('status', 'incomplete')->count(),
                    'total_minutes' => $present->sum('worked_minutes'),
                    'avg_minutes' => $present->count() > 0 ? (int) round($present->sum('worked_minutes') / $present->count()) : null,
                    'total_overtime_minutes' => $present->sum('overtime_minutes'),
                    'total_shortfall_minutes' => $present->sum('shortfall_minutes'),
                ];
            })
            ->sortByDesc('total_minutes')
            ->values();

        return view('admin.attendance-report.index', $viewData + [
            'mode' => 'summary',
            'summary' => $summary,
            'chartData' => [
                'labels' => $summary->take(20)->map(fn ($s) => $s['user']?->name ?? '—')->all(),
                'values' => $summary->take(20)->map(fn ($s) => round($s['total_minutes'] / 60, 2))->all(),
            ],
        ]);
    }

    /**
     * Build one row per (user, calendar day) in the requested range, classifying
     * each day as day_off / on_leave / present / incomplete / remote / absent
     * and computing overtime/shortfall for complete "present" days against the
     * employee's effective work schedule for that weekday.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<string, Collection<int, FaceDeviceEvent>>  $eventsByUserDay
     * @param  Collection<int, Collection<int, LeaveRequest>>  $leavesByUser
     */
    private function buildDailyEntries(
        Collection $users,
        Carbon $dateFrom,
        Carbon $dateTo,
        Collection $eventsByUserDay,
        Collection $leavesByUser,
        WorkScheduleResolver $resolver
    ): Collection {
        $rows = collect();

        foreach ($users as $user) {
            $schedule = $resolver->for($user);
            $leaves = $leavesByUser->get($user->id, collect());

            for ($date = $dateFrom->copy()->startOfDay(); $date->lte($dateTo); $date->addDay()) {
                $day = $schedule[$date->dayOfWeek];
                $dayEvents = $eventsByUserDay->get($user->id.'|'.$date->toDateString());

                $checkIn = $dayEvents?->min('event_time');
                $checkOut = $dayEvents && $dayEvents->count() > 1 ? $dayEvents->max('event_time') : null;
                $workedMinutes = $checkOut ? $checkIn->diffInMinutes($checkOut) : null;

                $status = $this->classifyDay($day, $leaves, $date, $dayEvents, $user);

                $scheduledMinutes = $resolver->scheduledMinutes($day);
                $overtime = null;
                $shortfall = null;

                if ($status === 'present') {
                    $overtime = max(0, $workedMinutes - $scheduledMinutes);
                    $shortfall = max(0, $scheduledMinutes - $workedMinutes);
                }

                $rows->push([
                    'user_id' => $user->id,
                    'user' => $user,
                    'date' => $date->toDateString(),
                    'status' => $status,
                    'scheduled_start' => $day['start_time'],
                    'scheduled_end' => $day['end_time'],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'worked_minutes' => $workedMinutes,
                    'overtime_minutes' => $overtime,
                    'shortfall_minutes' => $shortfall,
                ]);
            }
        }

        return $rows;
    }

    /**
     * @param  array{is_day_off: bool, start_time: ?string, end_time: ?string}  $day
     * @param  Collection<int, LeaveRequest>  $leaves
     * @param  ?Collection<int, FaceDeviceEvent>  $dayEvents
     */
    private function classifyDay(array $day, Collection $leaves, Carbon $date, ?Collection $dayEvents, User $user): string
    {
        if ($day['is_day_off']) {
            return 'day_off';
        }

        $onLeave = $leaves->contains(fn (LeaveRequest $leave) => $date->betweenIncluded($leave->from_date, $leave->to_date));

        if ($onLeave) {
            return 'on_leave';
        }

        if ($dayEvents && $dayEvents->count() > 1) {
            return 'present';
        }

        if ($dayEvents && $dayEvents->count() === 1) {
            return 'incomplete';
        }

        return $user->is_remote ? 'remote' : 'absent';
    }
}
