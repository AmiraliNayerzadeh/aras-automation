<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A manual override of one employee's check-in/out for one day (e.g. the power
 * was out at closing time). It replaces what the device recorded for that day
 * in every report; the raw face_device_events stay untouched, so deleting the
 * correction restores the original figures.
 */
#[Fillable(['user_id', 'date', 'check_in', 'check_out', 'reason', 'edited_by'])]
class AttendanceCorrection extends Model
{
    use LogsActivity;

    /**
     * Deliberately not a role or permission: only this one account may change
     * attendance times, so it can't be granted by editing roles.
     */
    public const EDITOR_EMAIL = 'amiralinayerzadeh@gmail.com';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'date', 'check_in', 'check_out', 'reason'])
            ->logOnlyDirty()
            ->useLogName('attendance_correction');
    }

    public static function canBeEditedBy(User $user): bool
    {
        return strcasecmp($user->email, self::EDITOR_EMAIL) === 0;
    }
}
