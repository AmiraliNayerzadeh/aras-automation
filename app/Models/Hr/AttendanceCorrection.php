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
     * Deliberately not a role or permission: only these accounts may change
     * attendance times, so it can't be granted by editing roles.
     */
    public const EDITOR_EMAILS = [
        'amiralinayerzadeh@gmail.com',
        'hokmabadi.morteza63@gmail.com',
    ];

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
        return in_array(mb_strtolower((string) $user->email), self::EDITOR_EMAILS, true);
    }
}
