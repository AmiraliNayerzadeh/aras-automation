<?php

namespace App\Models\Merchandising;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name', 'food_account_code', 'cleaning_account_code', 'tax_code', 'type', 'city', 'region',
    'address', 'phone', 'latitude', 'longitude', 'is_vip', 'status', 'is_verified',
    'legacy_account_id', 'notes', 'created_by',
])]
class Store extends Model
{
    use LogsActivity, SoftDeletes;

    public const TYPES = ['general', 'chain', 'supermarket', 'grocery', 'household', 'pharmacy', 'horeca', 'restaurant', 'bakery', 'gas_station'];

    public const STATUSES = ['active', 'suspended', 'inactive'];

    protected function casts(): array
    {
        return [
            'is_vip' => 'boolean',
            'is_verified' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logFillable()->useLogName('store');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('food_account_code', 'like', "{$term}%")
            ->orWhere('cleaning_account_code', 'like', "{$term}%")
            ->orWhere('city', 'like', "%{$term}%")
            ->orWhere('region', 'like', "%{$term}%")
            ->orWhere('address', 'like', "%{$term}%"));
    }

    /**
     * Both ERP codes, or the shared one when a chain uses a single account.
     */
    public function codes(): string
    {
        return collect([$this->food_account_code, $this->cleaning_account_code])->filter()->unique()->implode(' / ');
    }
}
