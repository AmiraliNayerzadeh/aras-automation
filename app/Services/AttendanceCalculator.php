<?php

namespace App\Services;

use App\Enums\RequestStatus;
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
 * page and the nightly PDF report so both stay in sync.
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

        $rows = collect();

        foreach ($users as $user) {
            $schedule = $this->resolver->for($user);
            $leaves = $leavesByUser->get($user->id, collect());

            for ($date = $dateFrom->copy()->startOfDay(); $date->lte($dateTo); $date->addDay()) {
                $day = $schedule[$date->dayOfWeek];
                $dayEvents = $eventsByUserDay->get($user->id.'|'.$date->toDateString());

                $checkIn = $dayEvents?->min('event_time');
                $checkOut = $dayEvents && $dayEvents->count() > 1 ? $dayEvents->max('event_time') : null;
                $workedMinutes = $checkOut ? $checkIn->diffInMinutes($checkOut) : null;

                $status = $this->classifyDay($day, $leaves, $date, $dayEvents, $user);

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
