<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'type', 'name', 'legal_name', 'code', 'tax_id', 'phone', 'email',
    'address', 'city', 'country', 'is_active', 'notes',
])]
class BusinessPartner extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('business_partner');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BusinessPartnerContact::class);
    }
}
