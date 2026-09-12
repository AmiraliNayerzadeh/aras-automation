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
        if ($folder->is_confidential) {
            return $user->is($folder->owner);
        }

        return $user->is($folder->owner) || $user->can('files.manage');
    }

    public function delete(User $user, Folder $folder): bool
    {
        if ($folder->is_confidential) {
            return $user->is($folder->owner);
        }

        return $user->is($folder->owner) || $user->can('files.manage');
    }

    /**
     * Marking/unmarking is a separate, narrower ability from update(): the
     * owner can always toggle it, and a files.mark_confidential holder can
     * toggle it only on a folder they can currently see — once a folder
     * becomes confidential and they're not the owner/grantee, they lose this
     * too, by design (no backdoor via a guessed/known id).
     */
    public function markConfidential(User $user, Folder $folder): bool
    {
        return $user->is($folder->owner)
            || ($user->can('files.mark_confidential') && $folder->isVisibleTo($user));
    }
}
