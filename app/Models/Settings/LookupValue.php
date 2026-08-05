<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['lookup_type_id', 'code', 'label', 'color', 'sort_order', 'is_active', 'meta'])]
class LookupValue extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'label' => 'array',
            'meta' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('lookup_value');
    }

    public function lookupType(): BelongsTo
    {
        return $this->belongsTo(LookupType::class);
    }
}
