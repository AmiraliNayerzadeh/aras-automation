<?php

namespace App\Models\Hr;

use App\Contracts\Approvable;
use App\Enums\ApprovalStepRole;
use App\Enums\RequestStatus;
use App\Models\Concerns\HasApprovalChain;
use App\Models\Settings\LookupValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id', 'lookup_value_id', 'substitute_user_id', 'from_date', 'to_date',
    'start_time', 'end_time', 'day_count', 'description',
    'status', 'submitted_at', 'decided_at',
])]
class LeaveRequest extends Model implements Approvable
{
    use HasApprovalChain, LogsActivity, SoftDeletes;

    protected $attributes = [
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
            'status' => RequestStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('leave_request');
    }

    /**
     * @return array<int, ApprovalStepRole>
     */
    public function approvalStepRoles(): array
    {
        return [ApprovalStepRole::Manager, ApprovalStepRole::Hr, ApprovalStepRole::Ceo];
    }

    public function permissionPrefix(): string
    {
        return 'leaves';
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'lookup_value_id');
    }

    public function substitute(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_user_id');
    }
}
