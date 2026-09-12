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
        if ($file->is_confidential) {
            return $user->is($file->owner);
        }

        return $user->is($file->owner) || $user->can('files.manage');
    }

    public function delete(User $user, FileEntry $file): bool
    {
        if ($file->is_confidential) {
            return $user->is($file->owner);
        }

        return $user->is($file->owner) || $user->can('files.manage');
    }

    /**
     * Marking/unmarking is a separate, narrower ability from update(): the
     * owner can always toggle it, and a files.mark_confidential holder can
     * toggle it only on a file they can currently see — once a file becomes
     * confidential and they're not the owner/grantee, they lose this too,
     * by design (no backdoor via a guessed/known id).
     */
    public function markConfidential(User $user, FileEntry $file): bool
    {
        return $user->is($file->owner)
            || ($user->can('files.mark_confidential') && $file->isVisibleTo($user));
    }
}
