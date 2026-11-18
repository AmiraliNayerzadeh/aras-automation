<?php

namespace App\Models\Merchandising;

use App\Models\FileManager\FileEntry;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A photo or video for a visit. It's uploaded in chunks before the visit
 * exists, then attached and filed into the file manager on submit.
 */
#[Fillable([
    'token', 'user_id', 'visit_id', 'file_entry_id', 'kind', 'original_name', 'mime_type',
    'size_bytes', 'received_bytes', 'temp_path', 'completed_at',
])]
class Upload extends Model
{
    protected $table = 'merchandising_uploads';

    public const KINDS = ['before', 'after', 'extra'];

    /** Videos straight from a phone can be large; this keeps one visit from filling the disk. */
    public const MAX_BYTES = 500 * 1024 * 1024;

    /** Under nginx's default 1 MB request limit, so no server change is needed. */
    public const CHUNK_BYTES = 768 * 1024;

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'received_bytes' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function fileEntry(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class);
    }

    public function isVideo(): bool
    {
        return str_starts_with((string) $this->mime_type, 'video/');
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }
}
