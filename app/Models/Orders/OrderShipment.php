<?php

namespace App\Models\Orders;

use App\Models\Settings\LookupValue;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'order_id', 'carrier_name', 'tracking_number', 'vehicle_plate', 'driver_name',
    'transport_method_lookup_value_id', 'departure_date', 'expected_arrival_date',
    'actual_arrival_date', 'cost', 'note',
])]
class OrderShipment extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'expected_arrival_date' => 'date',
            'actual_arrival_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('order_shipment');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transportMethod(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'transport_method_lookup_value_id');
    }
}
