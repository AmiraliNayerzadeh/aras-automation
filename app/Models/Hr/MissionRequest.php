<?php

namespace App\Models\Hr;

use App\Contracts\Approvable;
use App\Enums\ApprovalStepRole;
use App\Enums\RequestStatus;
use App\Models\Concerns\HasApprovalChain;
use App\Models\Settings\LookupValue;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id', 'lookup_value_id', 'destination', 'from_date', 'to_date', 'purpose',
    'outbound_transport', 'return_transport', 'estimated_cost', 'actual_cost',
    'currency', 'mission_report',
    'status', 'submitted_at', 'decided_at',
])]
class MissionRequest extends Model implements Approvable
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
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
            'status' => RequestStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('mission_request');
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
        return 'missions';
    }

    public function missionType(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'lookup_value_id');
    }
}
