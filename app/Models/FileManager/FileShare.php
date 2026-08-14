<?php

namespace App\Models\FileManager;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Permission\Models\Role;

#[Fillable(['shareable_type', 'shareable_id', 'grantee_type', 'grantee_id', 'created_by_id'])]
class FileShare extends Model
{
    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function grantee(): User|Role|null
    {
        return match ($this->grantee_type) {
            'user' => User::find($this->grantee_id),
            'role' => Role::find($this->grantee_id),
            default => null,
        };
    }

    public function label(): string
    {
        return match ($this->grantee_type) {
            'user' => $this->grantee()?->name ?? __('files.deleted_user'),
            'role' => $this->grantee()?->name ?? __('files.deleted_role'),
            default => __('files.share_everyone'),
        };
    }
}
