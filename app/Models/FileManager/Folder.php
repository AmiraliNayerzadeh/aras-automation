<?php

namespace App\Models\FileManager;

use App\Models\FileManager\Concerns\HasFileShares;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['name', 'parent_id', 'owner_id'])]
class Folder extends Model
{
    use HasFileShares, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('folder');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(FileEntry::class, 'folder_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return Collection<int, self>
     */
    public function ancestors(): Collection
    {
        $items = collect();
        $current = $this->parent;

        while ($current) {
            $items->prepend($current);
            $current = $current->parent;
        }

        return $items;
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->hasDirectAccess($user)) {
            return true;
        }

        return (bool) $this->parent?->isVisibleTo($user);
    }
}
