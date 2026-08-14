<?php

namespace App\Policies;

use App\Models\FileManager\FileEntry;
use App\Models\User;

class FileEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('files.create') || $user->can('files.view_all');
    }

    public function view(User $user, FileEntry $file): bool
    {
        return $file->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->can('files.create');
    }

    public function update(User $user, FileEntry $file): bool
    {
        return $user->is($file->owner) || $user->can('files.manage');
    }

    public function delete(User $user, FileEntry $file): bool
    {
        return $user->is($file->owner) || $user->can('files.manage');
    }
}
