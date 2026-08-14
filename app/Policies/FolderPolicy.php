<?php

namespace App\Policies;

use App\Models\FileManager\Folder;
use App\Models\User;

class FolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('files.create') || $user->can('files.view_all');
    }

    public function view(User $user, Folder $folder): bool
    {
        return $folder->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->can('files.create');
    }

    public function update(User $user, Folder $folder): bool
    {
        return $user->is($folder->owner) || $user->can('files.manage');
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $user->is($folder->owner) || $user->can('files.manage');
    }
}
