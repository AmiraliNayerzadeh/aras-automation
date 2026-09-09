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

    public function isSpreadsheet(): bool
    {
        return in_array($this->mime_type, [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/csv',
        ], true) || $this->hasExtension(['xlsx', 'xls', 'csv']);
    }

    /**
     * True only for modern .docx (docx-preview, used for in-browser preview,
     * can't render the older binary .doc format).
     */
    public function isWordDocument(): bool
    {
        return $this->mime_type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            || $this->hasExtension(['docx']);
    }

    public function isLegacyWordDocument(): bool
    {
        return $this->mime_type === 'application/msword' || $this->hasExtension(['doc']);
    }

    public function isPresentation(): bool
    {
        return in_array($this->mime_type, [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ], true) || $this->hasExtension(['pptx', 'ppt']);
    }

    public function isArchive(): bool
    {
        return in_array($this->mime_type, [
            'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed',
            'application/x-tar', 'application/gzip',
        ], true) || $this->hasExtension(['zip', 'rar', '7z', 'tar', 'gz']);
    }

    public function iconClass(): string
    {
        return match (true) {
            $this->isPdf() => 'ri-file-pdf-2-line',
            $this->isSpreadsheet() => 'ri-file-excel-2-line',
            $this->isWordDocument(), $this->isLegacyWordDocument() => 'ri-file-word-2-line',
            $this->isPresentation() => 'ri-file-ppt-2-line',
            $this->isArchive() => 'ri-file-zip-line',
            Str::startsWith((string) $this->mime_type, 'audio/') => 'ri-file-music-line',
            Str::startsWith((string) $this->mime_type, 'video/') => 'ri-file-video-line',
            $this->mime_type === 'text/plain' => 'ri-file-text-line',
            default => 'ri-file-3-line',
        };
    }

    public function iconColorClass(): string
    {
        return match (true) {
            $this->isPdf() => 'text-danger-main',
            $this->isSpreadsheet() => 'text-success-main',
            $this->isWordDocument(), $this->isLegacyWordDocument() => 'text-info-main',
            $this->isPresentation() => 'text-warning-main',
            $this->isArchive() => 'text-neutral-500',
            default => 'text-neutral-400',
        };
    }

    private function hasExtension(array $extensions): bool
    {
        $ext = strtolower(pathinfo((string) $this->original_name, PATHINFO_EXTENSION));

        return in_array($ext, $extensions, true);
    }
}
