<?php

namespace App\Models\FileManager\Concerns;

use App\Models\FileManager\FileShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFileShares
{
    public function shares(): MorphMany
    {
        return $this->morphMany(FileShare::class, 'shareable');
    }

    public function scopeSharedWithUser(Builder $query, User $user): Builder
    {
        $roleIds = $user->roles->pluck('id');

        return $query->whereHas('shares', function (Builder $q) use ($user, $roleIds) {
            $q->where('grantee_type', 'everyone')
                ->orWhere(fn (Builder $qq) => $qq->where('grantee_type', 'user')->where('grantee_id', $user->id))
                ->orWhere(fn (Builder $qq) => $qq->where('grantee_type', 'role')->whereIn('grantee_id', $roleIds))
                ->when($user->department_id, fn (Builder $qq) => $qq->orWhere(
                    fn (Builder $qqq) => $qqq->where('grantee_type', 'department')->where('grantee_id', $user->department_id)
                ))
                ->when($user->position_id, fn (Builder $qq) => $qq->orWhere(
                    fn (Builder $qqq) => $qqq->where('grantee_type', 'position')->where('grantee_id', $user->position_id)
                ));
        });
    }

    protected function hasDirectAccess(User $user): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }

        if ($user->can('files.view_all')) {
            return true;
        }

        $roleIds = $user->roles->pluck('id');

        return $this->shares()
            ->where(function (Builder $query) use ($user, $roleIds) {
                $query->where('grantee_type', 'everyone')
                    ->orWhere(fn (Builder $q) => $q->where('grantee_type', 'user')->where('grantee_id', $user->id))
                    ->orWhere(fn (Builder $q) => $q->where('grantee_type', 'role')->whereIn('grantee_id', $roleIds))
                    ->when($user->department_id, fn (Builder $q) => $q->orWhere(
                        fn (Builder $qq) => $qq->where('grantee_type', 'department')->where('grantee_id', $user->department_id)
                    ))
                    ->when($user->position_id, fn (Builder $q) => $q->orWhere(
                        fn (Builder $qq) => $qq->where('grantee_type', 'position')->where('grantee_id', $user->position_id)
                    ));
            })
            ->exists();
    }
}
