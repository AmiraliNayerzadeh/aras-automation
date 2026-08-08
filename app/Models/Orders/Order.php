<?php

namespace App\Models\Orders;

use App\Enums\OrderType;
use App\Models\Partners\BusinessPartner;
use App\Models\Settings\LookupValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'type', 'order_number', 'order_date', 'business_partner_id', 'currency', 'amount',
    'description', 'current_stage_lookup_value_id', 'current_stage_since', 'is_closed', 'created_by_id',
])]
class Order extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'order_date' => 'date',
            'amount' => 'decimal:2',
            'current_stage_since' => 'datetime',
            'is_closed' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('order');
    }

    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('sort_order');
    }

    public function stageLogs(): HasMany
    {
        return $this->hasMany(OrderStageLog::class)->orderBy('occurred_at');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'current_stage_lookup_value_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(OrderShipment::class);
    }

    public function isEditable(): bool
    {
        return ! $this->is_closed && $this->stageLogs()->count() <= 1;
    }
}
