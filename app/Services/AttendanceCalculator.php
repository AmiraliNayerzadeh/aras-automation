<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Models\Hr\AttendanceCorrection;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\Hr\LeaveRequest;
use App\Models\User;
use App\Support\WorkScheduleResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds one row per (user, calendar day), classifying each day as
 * day_off / on_leave / present / incomplete / remote / absent and computing
 * overtime/shortfall for complete "present" days against the employee's
 * effective work schedule for that weekday. Shared by the attendance report
 * page, its Excel export and the nightly PDF report so all stay in sync. An
 * AttendanceCorrection replaces what the device recorded for that day.
 */
class AttendanceCalculator
{
    public function __construct(private readonly WorkScheduleResolver $resolver)
    {
    }

    /**
     * @param  Collection<int, User>  $users  Must have the "workShifts" relation eager-loaded.
     */
    public function forDateRange(Collection $users, Carbon $dateFrom, Carbon $dateTo): Collection
    {
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

        $correctionsByUserDay = AttendanceCorrection::query()
            ->whereIn('user_id', $userIds)
            ->whereDate('date', '>=', $dateFrom->toDateString())
            ->whereDate('date', '<=', $dateTo->toDateString())
            ->get()
            ->keyBy(fn (AttendanceCorrection $c) => $c->user_id.'|'.$c->date->toDateString());

        $rows = collect();

        foreach ($users as $user) {
            $schedule = $this->resolver->for($user);
            $leaves = $leavesByUser->get($user->id, collect());

            for ($date = $dateFrom->copy()->startOfDay(); $date->lte($dateTo); $date->addDay()) {
                $day = $schedule[$date->dayOfWeek];
                $dayEvents = $eventsByUserDay->get($user->id.'|'.$date->toDateString());

                $correction = $correctionsByUserDay->get($user->id.'|'.$date->toDateString());

                $checkIn = $correction ? $correction->check_in : $dayEvents?->min('event_time');
                $checkOut = $correction
                    ? $correction->check_out
                    : ($dayEvents && $dayEvents->count() > 1 ? $dayEvents->max('event_time') : null);
                $workedMinutes = $checkIn && $checkOut ? (int) $checkIn->diffInMinutes($checkOut) : null;

                $status = $this->classifyDay($day, $leaves, $date, $checkIn, $checkOut, $user);

                $scheduledMinutes = $this->resolver->scheduledMinutes($day);
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
                    'correction' => $correction,
                ]);
            }
        }

        return $rows;
    }

    /**
     * Per-employee totals for rows from forDateRange(), busiest first.
     */
    public function summarize(Collection $daily): Collection
    {
        return $daily
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
    }

    /**
     * @param  array{is_day_off: bool, start_time: ?string, end_time: ?string}  $day
     * @param  Collection<int, LeaveRequest>  $leaves
     */
    private function classifyDay(array $day, Collection $leaves, Carbon $date, ?Carbon $checkIn, ?Carbon $checkOut, User $user): string
    {
        if ($day['is_day_off']) {
            return 'day_off';
        }

        $onLeave = $leaves->contains(fn (LeaveRequest $leave) => $date->betweenIncluded($leave->from_date, $leave->to_date));

        if ($onLeave) {
            return 'on_leave';
        }

        if ($checkIn && $checkOut) {
            return 'present';
        }

        if ($checkIn || $checkOut) {
            return 'incomplete';
        }

        return $user->is_remote ? 'remote' : 'absent';
    }
}
