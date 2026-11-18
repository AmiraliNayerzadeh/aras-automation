<?php

namespace App\Services\Merchandising;

use App\Enums\MerchandisingDivision;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Creates or updates products from the ERP item master, matched on the item
 * code (stored as the product SKU), so re-importing a newer export never
 * duplicates anything. Prices, brands and stock are left alone.
 */
class ProductCatalogImporter
{
    /** ERP main groups and the division each belongs to; anything else stays unassigned. */
    public const DIVISIONS = [
        'TEA & COFFEE & SWEET' => MerchandisingDivision::TopSun,
        'CEREALS & CANNED FOOD' => MerchandisingDivision::TopSun,
        'MARGARINE & GHEE & OIL & CHEESE & INSTANT YEAST' => MerchandisingDivision::TopSun,
        'KETCHUP & TOMATO PASTA' => MerchandisingDivision::TopSun,
        'MAYONNAISE' => MerchandisingDivision::TopSun,
        'SEMI PREPARED FOOD' => MerchandisingDivision::TopSun,
        'WASHING' => MerchandisingDivision::Unilever,
        'TOILET PAPER & KITCHEN PAPER & TISSUE & WIPES & SANITARY NAPKIN & BABY DIPER' => MerchandisingDivision::Unilever,
        'RUSSIA UNILEVER' => MerchandisingDivision::Unilever,
        'RUSSIA UNILEVER 2' => MerchandisingDivision::Unilever,
        'RUSSIA UNILEVER 3' => MerchandisingDivision::Unilever,
        'UNILEVER EN' => MerchandisingDivision::Unilever,
        'SPRAY' => MerchandisingDivision::Unilever,
        'DISPOSABLE DISHES' => MerchandisingDivision::Unilever,
        'PLASTIC' => MerchandisingDivision::Unilever,
    ];

    /** Items the ERP has taken off sale. */
    public const DELISTED_GROUP = 'DELIST';

    /** @var array<string, ProductCategory> */
    private array $categories = [];

    /**
     * @param  iterable<array<string, ?string>>  $rows
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function import(iterable $rows, ?string $imageDirectory = null): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        $this->categories = ProductCategory::withTrashed()->get()
            ->keyBy(fn (ProductCategory $c) => self::normalise($c->title))->all();
        $products = Product::withTrashed()->get()->keyBy('sku');
        $barcodes = Product::withTrashed()->whereNotNull('barcode')->pluck('sku', 'barcode')->all();

        DB::transaction(function () use ($rows, $imageDirectory, $products, &$barcodes, &$stats) {
            foreach ($rows as $index => $row) {
                $code = $row['code'] ?? $row['item_code'] ?? null;
                $name = $row['name'] ?? $row['item_name'] ?? null;

                if (! $code || ! $name) {
                    $stats['skipped']++;
                    $stats['errors'][] = 'Row '.($index + 2).': missing item code or name.';

                    continue;
                }

                $group = self::normalise($row['main_group'] ?? $row['item_main_group_desc'] ?? '');
                $product = $products->get($code);

                if ($product?->trashed()) {
                    $stats['skipped']++;

                    continue;
                }

                $barcode = $row['barcode'] ?? $row['item_barcode'] ?? null;
                $barcodeFree = $barcode && (! isset($barcodes[$barcode]) || $barcodes[$barcode] === $code);

                $attributes = array_filter([
                    'title' => $name,
                    'unit' => $row['unit'] ?? $row['unit_1'] ?? null,
                    'line' => $row['line'] ?? $row['itemgroupdesc'] ?? $row['item_group_desc'] ?? null,
                    'category_id' => $group !== '' ? $this->category($group)->id : null,
                    'barcode' => $barcodeFree ? $barcode : null,
                ], fn ($value) => $value !== null);
                $attributes['is_active'] = $group !== self::DELISTED_GROUP;

                if ($product) {
                    $product->fill($attributes);

                    if ($product->isDirty()) {
                        $product->save();
                        $stats['updated']++;
                    }
                } else {
                    $product = Product::create($attributes + ['sku' => $code]);
                    $products->put($code, $product);
                    $stats['created']++;
                }

                if ($barcodeFree) {
                    $barcodes[$barcode] = $code;
                }

                $this->attachImage($product, $imageDirectory);
            }
        });

        return $stats;
    }

    public static function normalise(?string $value): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', (string) $value)));
    }

    private function category(string $group): ProductCategory
    {
        $category = $this->categories[$group] ??= ProductCategory::create(['title' => $group, 'is_active' => true]);

        $category->trashed() && $category->restore();

        // An admin's own division choice wins over the default mapping.
        if ($category->division === null && isset(self::DIVISIONS[$group])) {
            $category->forceFill(['division' => self::DIVISIONS[$group]->value])->save();
        }

        return $category;
    }

    private function attachImage(Product $product, ?string $directory): void
    {
        if (! $directory || $product->image_path) {
            return;
        }

        $source = $directory.DIRECTORY_SEPARATOR.$product->sku.'.jpg';

        if (is_file($source)) {
            $path = Storage::disk('public')->putFileAs('products', $source, $product->sku.'.jpg');
            $product->forceFill(['image_path' => $path])->save();
        }
    }
}
