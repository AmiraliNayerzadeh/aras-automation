<?php

namespace App\Exports;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(protected array $filters) {}

    public function query(): Builder
    {
        return Product::query()->with(['category', 'brand'])->filter($this->filters)->orderBy('title');
    }

    public function headings(): array
    {
        return [
            __('products.field_sku'),
            __('products.field_title'),
            __('products.field_category'),
            __('products.field_brand'),
            __('products.field_price'),
            __('products.field_stock'),
        ];
    }

    public function map($product): array
    {
        return [
            $product->sku,
            $product->title,
            $product->category?->title,
            $product->brand?->title,
            $product->price !== null ? (float) $product->price : null,
            (float) ($product->stock_balances_sum_quantity ?? 0),
        ];
    }
}
