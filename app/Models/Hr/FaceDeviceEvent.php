<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'employee_no', 'person_name',
    'device_serial', 'device_ip',
    'major_event', 'minor_event', 'verify_mode', 'attendance_status',
    'event_time', 'picture_path', 'raw_payload',
])]
class FaceDeviceEvent extends Model
{
    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
