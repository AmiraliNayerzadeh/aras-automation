<?php

namespace App\Models\FileManager;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['file_entry_id', 'user_id', 'body'])]
class FileComment extends Model
{
    public function fileEntry(): BelongsTo
    {
        return $this->belongsTo(FileEntry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
