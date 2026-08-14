<?php

namespace App\Models\FileManager;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['file_entry_id', 'original_name', 'file_path', 'mime_type', 'size_bytes', 'uploaded_by_id'])]
class FileVersion extends Model
{
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function fileEntry(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
