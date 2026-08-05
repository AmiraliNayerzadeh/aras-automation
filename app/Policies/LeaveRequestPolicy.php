<?php

namespace App\Policies;

use App\Models\Hr\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leaves.create') || $user->can('leaves.view_all');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->is($leaveRequest->user)
            || $user->can('leaves.view_all')
            || $leaveRequest->user->manager_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('leaves.create');
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->is($leaveRequest->user) && $leaveRequest->isEditable();
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return ($user->is($leaveRequest->user) || $user->can('leaves.cancel_any'))
            && ! $leaveRequest->status->isTerminal();
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    public function forceDelete(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }
}
