<?php

namespace App\Models\Orders;

use App\Models\Document;
use App\Models\Settings\LookupValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'order_id', 'lookup_value_id', 'responsible_user_id', 'created_by_id',
    'occurred_at', 'description', 'cost', 'comment', 'is_skipped',
])]
class OrderStageLog extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'cost' => 'decimal:2',
            'is_skipped' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('order_stage_log');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lookupValue(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
