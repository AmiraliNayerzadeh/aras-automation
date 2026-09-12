<?php

namespace App\Models\FileManager\Concerns;

use App\Models\FileManager\FileShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasFileShares
{
    public function shares(): MorphMany
    {
        return $this->morphMany(FileShare::class, 'shareable');
    }

    /**
     * Items with no explicit share defined are open to everyone in the company
     * by default. The moment ANY share is added, the item becomes restricted to
     * its owner, files.view_all admins, and the explicit grantees only — a share
     * on this item always wins, regardless of how open/restricted a containing
     * folder is.
     *
     * An item marked `is_confidential` is a stricter layer on top of all of that:
     * it is visible ONLY to its owner and its explicit grantees — never via the
     * "no shares = open to everyone" fallback, and never via files.view_all. This
     * is intentional and absolute: there is no recovery path for a confidential
     * item whose owner leaves and whose grantee list goes stale.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $roleIds = $user->roles->pluck('id');

        $ownedOrGranted = fn (Builder $q) => $q->where('owner_id', $user->id)
            ->orWhereHas('shares', fn (Builder $sq) => $this->applyGranteeMatch($sq, $user, $roleIds));

        if ($user->can('files.view_all')) {
            return $query->where(fn (Builder $q) => $q->where('is_confidential', false)->orWhere($ownedOrGranted));
        }

        return $query->where(function (Builder $q) use ($user, $roleIds, $ownedOrGranted) {
            $q->where(function (Builder $qq) use ($user, $roleIds) {
                $qq->where('is_confidential', false)
                    ->where(fn (Builder $qqq) => $qqq->where('owner_id', $user->id)
                        ->orWhereDoesntHave('shares')
                        ->orWhereHas('shares', fn (Builder $sq) => $this->applyGranteeMatch($sq, $user, $roleIds)));
            })->orWhere($ownedOrGranted);
        });
    }

    /**
     * Items explicitly shared with the user (used for the "Shared with Me" tab) —
     * unlike scopeVisibleTo, this deliberately excludes unrestricted (open by
     * default) items, since it's meant to list only what was actively shared.
     */
    public function scopeSharedWithUser(Builder $query, User $user): Builder
    {
        $roleIds = $user->roles->pluck('id');

        return $query->whereHas('shares', fn (Builder $q) => $this->applyGranteeMatch($q, $user, $roleIds));
    }

    /**
     * @param  Collection<int, int>  $roleIds
     */
    protected function applyGranteeMatch(Builder $query, User $user, Collection $roleIds): Builder
    {
        return $query->where('grantee_type', 'everyone')
            ->orWhere(fn (Builder $q) => $q->where('grantee_type', 'user')->where('grantee_id', $user->id))
            ->orWhere(fn (Builder $q) => $q->where('grantee_type', 'role')->whereIn('grantee_id', $roleIds))
            ->when($user->department_id, fn (Builder $q) => $q->orWhere(
                fn (Builder $qq) => $qq->where('grantee_type', 'department')->where('grantee_id', $user->department_id)
            ))
            ->when($user->position_id, fn (Builder $q) => $q->orWhere(
                fn (Builder $qq) => $qq->where('grantee_type', 'position')->where('grantee_id', $user->position_id)
            ));
    }

    /**
     * Single-record check mirroring scopeVisibleTo exactly, so listing and
     * single-item authorization (policies) never drift apart.
     */
    public function isVisibleTo(User $user): bool
    {
        return static::query()->whereKey($this->getKey())->visibleTo($user)->exists();
    }
}
