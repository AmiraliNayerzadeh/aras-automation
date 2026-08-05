<?php

namespace App\Models\Workflow;

use App\Enums\AttachmentKind;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'attachable_type', 'attachable_id', 'kind', 'file_path',
    'original_name', 'mime_type', 'size_bytes', 'uploaded_by_id',
])]
class RequestAttachment extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'kind' => AttachmentKind::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('request_attachment');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
