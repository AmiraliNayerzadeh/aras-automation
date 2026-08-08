<?php

namespace App\Models\Products;

use App\Models\Warehouse\WarehouseStockBalance;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'sku', 'barcode', 'title', 'subtitle', 'category_id', 'brand_id',
    'unit', 'package_quantity', 'price', 'description', 'image_path', 'is_active',
])]
class Product extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sku', 'barcode', 'title', 'category_id', 'brand_id', 'unit', 'package_quantity', 'price', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('product');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path ? Storage::url($this->image_path) : null);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    public function stockBalances(): HasMany
    {
        return $this->hasMany(WarehouseStockBalance::class);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->withSum('stockBalances', 'quantity')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($qq) use ($search) {
                $qq->where('title', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            }))
            ->when($filters['category_id'] ?? null, fn ($q, $value) => $q->where('category_id', $value))
            ->when($filters['brand_id'] ?? null, fn ($q, $value) => $q->where('brand_id', $value))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null, fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->when(($filters['stock_status'] ?? null) === 'in_stock', fn ($q) => $q->having('stock_balances_sum_quantity', '>', 0))
            ->when(($filters['stock_status'] ?? null) === 'out_of_stock', fn ($q) => $q->having(DB::raw('COALESCE(stock_balances_sum_quantity, 0)'), '<=', 0))
            ->when($filters['price_min'] ?? null, fn ($q, $value) => $q->where('price', '>=', $value))
            ->when($filters['price_max'] ?? null, fn ($q, $value) => $q->where('price', '<=', $value));
    }
}
