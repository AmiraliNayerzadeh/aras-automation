<?php

namespace App\Models\Merchandising;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'visit_id', 'product_id', 'product_name', 'division', 'stock_status',
    'shelf_position', 'price_tag', 'issues', 'needs_order',
])]
class VisitItem extends Model
{
    protected $table = 'merchandising_visit_items';

    public const STOCK_STATUSES = ['in_stock', 'low', 'out_of_stock', 'not_listed'];

    public const SHELF_POSITIONS = ['eye_level', 'middle', 'bottom', 'top', 'storage'];

    public const PRICE_TAGS = ['correct', 'missing', 'wrong'];

    protected function casts(): array
    {
        return [
            'issues' => 'array',
            'needs_order' => 'boolean',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function displayName(): string
    {
        return $this->product?->title ?? (string) $this->product_name;
    }
}
