<?php

namespace App\Models\Concerns;

use App\Enums\ApprovalStepStatus;
use App\Enums\RequestStatus;
use App\Models\User;
use App\Models\Workflow\ApprovalStep;
use App\Models\Workflow\RequestAttachment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasApprovalChain
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requester(): User
    {
        return $this->user;
    }

    public function approvalSteps(): MorphMany
    {
        return $this->morphMany(ApprovalStep::class, 'subject')->orderBy('step_order');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(RequestAttachment::class, 'attachable');
    }

    public function currentStep(): ?ApprovalStep
    {
        return $this->approvalSteps
            ->firstWhere('status', ApprovalStepStatus::Pending);
    }

    public function isEditable(): bool
    {
        return $this->status === RequestStatus::Draft;
    }

    public function isPending(): bool
    {
        return $this->status === RequestStatus::Pending;
    }
}
