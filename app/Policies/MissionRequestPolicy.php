<?php

namespace App\Policies;

use App\Models\Hr\MissionRequest;
use App\Models\User;

class MissionRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('missions.create') || $user->can('missions.view_all');
    }

    public function view(User $user, MissionRequest $missionRequest): bool
    {
        return $user->is($missionRequest->user)
            || $user->can('missions.view_all')
            || $missionRequest->user->manager_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('missions.create');
    }

    public function update(User $user, MissionRequest $missionRequest): bool
    {
        return $user->is($missionRequest->user) && $missionRequest->isEditable();
    }

    public function cancel(User $user, MissionRequest $missionRequest): bool
    {
        return ($user->is($missionRequest->user) || $user->can('missions.cancel_any'))
            && ! $missionRequest->status->isTerminal();
    }

    public function report(User $user, MissionRequest $missionRequest): bool
    {
        return $user->is($missionRequest->user)
            && $missionRequest->status->value === 'approved'
            && $missionRequest->to_date->isPast();
    }

    public function delete(User $user, MissionRequest $missionRequest): bool
    {
        return false;
    }

    public function forceDelete(User $user, MissionRequest $missionRequest): bool
    {
        return false;
    }
}
