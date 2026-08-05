<?php

namespace App\Models\Workflow;

use App\Enums\ApprovalStepRole;
use App\Enums\ApprovalStepStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'subject_type', 'subject_id', 'step_order', 'role', 'approver_id',
    'status', 'acted_by_id', 'acted_at', 'comment', 'system_note',
])]
class ApprovalStep extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'role' => ApprovalStepRole::class,
            'status' => ApprovalStepStatus::class,
            'acted_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('approval_step');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function actedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_id');
    }
}
