<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Resolves the effective weekly work schedule for an employee: their own
 * app/Models/Hr/WorkShift.php rows when configured, otherwise the company
 * default template (Mon–Fri 09:00–18:00, Sat 09:00–15:00, Sun off).
 */
class WorkScheduleResolver
{
    /**
     * Keyed by Carbon::dayOfWeek (0 = Sunday ... 6 = Saturday).
     *
     * @return array<int, array{is_day_off: bool, start_time: ?string, end_time: ?string}>
     */
    public function defaultTemplate(): array
    {
        $standard = ['is_day_off' => false, 'start_time' => '09:00', 'end_time' => '18:00'];

        return [
            0 => ['is_day_off' => true, 'start_time' => null, 'end_time' => null],
            1 => $standard,
            2 => $standard,
            3 => $standard,
            4 => $standard,
            5 => $standard,
            6 => ['is_day_off' => false, 'start_time' => '09:00', 'end_time' => '15:00'],
        ];
    }

    /**
     * @return array<int, array{is_day_off: bool, start_time: ?string, end_time: ?string}>
     */
    public function for(User $user): array
    {
        $custom = $user->workShifts->keyBy('day_of_week');

        if ($custom->isEmpty()) {
            return $this->defaultTemplate();
        }

        $schedule = $this->defaultTemplate();

        foreach ($custom as $dayOfWeek => $shift) {
            $schedule[$dayOfWeek] = [
                'is_day_off' => $shift->is_day_off,
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
            ];
        }

        return $schedule;
    }

    /**
     * @param  array{is_day_off: bool, start_time: ?string, end_time: ?string}  $day
     */
    public function scheduledMinutes(array $day): int
    {
        if ($day['is_day_off'] || ! $day['start_time'] || ! $day['end_time']) {
            return 0;
        }

        return Carbon::parse($day['start_time'])->diffInMinutes(Carbon::parse($day['end_time']));
    }
}
