<?php

namespace App\Policies;

use App\Models\Tasks\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.create') || $user->can('tasks.view_all');
    }

    public function view(User $user, Task $task): bool
    {
        return $task->assignees->contains('id', $user->id)
            || $user->is($task->createdBy)
            || $user->can('tasks.view_all');
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $task->assignees->contains('id', $user->id)
            || $user->is($task->createdBy)
            || $user->can('tasks.manage');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->is($task->createdBy) || $user->can('tasks.manage');
    }

    public function manageAttachment(User $user, Task $task, ?int $uploaderId = null): bool
    {
        return $user->id === $uploaderId
            || $user->is($task->createdBy)
            || $user->can('tasks.manage');
    }
}
