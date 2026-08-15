<?php

namespace App\Models\Hr;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'day_of_week', 'is_day_off', 'start_time', 'end_time'])]
class WorkShift extends Model
{
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_day_off' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
