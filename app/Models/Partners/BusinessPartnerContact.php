<?php

namespace App\Models\Partners;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['business_partner_id', 'name', 'position', 'phone', 'email', 'is_primary'])]
class BusinessPartnerContact extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('business_partner_contact');
    }

    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class);
    }
}
