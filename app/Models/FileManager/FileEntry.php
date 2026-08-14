<?php

namespace App\Models\FileManager;

use App\Models\FileManager\Concerns\HasFileShares;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'folder_id', 'owner_id', 'title', 'original_name', 'file_path',
    'mime_type', 'size_bytes', 'share_token', 'share_token_expires_at',
])]
class FileEntry extends Model
{
    use HasFileShares, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'share_token_expires_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('file_entry');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FileComment::class)->latest();
    }

    protected function humanSize(): Attribute
    {
        return Attribute::get(function () {
            $bytes = (float) $this->size_bytes;
            $units = ['B', 'KB', 'MB', 'GB'];
            $i = 0;

            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }

            return round($bytes, $i === 0 ? 0 : 1).' '.$units[$i];
        });
    }

    public function isImage(): bool
    {
        return Str::startsWith((string) $this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->hasDirectAccess($user)) {
            return true;
        }

        return (bool) $this->folder?->isVisibleTo($user);
    }
}
