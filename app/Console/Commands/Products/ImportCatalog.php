<?php

namespace App\Console\Commands\Products;

use App\Models\Products\Product;
use App\Models\Products\ProductBrand;
use App\Models\Products\ProductCategory;
use Generator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use SplFileObject;

#[Signature('products:import {categories : Path to categories CSV} {brands : Path to brands CSV} {products : Path to products CSV}')]
#[Description('One-time import of the product catalog (categories, brands, products) from the legacy system\'s CSV exports. Stock always starts at zero.')]
class ImportCatalog extends Command
{
    public function handle(): int
    {
        if (Product::withTrashed()->exists() && ! $this->confirm('Products already exist. Continue and import anyway?')) {
            return self::FAILURE;
        }

        $categoryMap = $this->importCategories($this->argument('categories'));
        $this->info('Imported '.count($categoryMap).' categories.');

        $brandMap = $this->importBrands($this->argument('brands'));
        $this->info('Imported '.count($brandMap).' brands.');

        $productCount = $this->importProducts($this->argument('products'), $categoryMap, $brandMap);
        $this->info("Imported {$productCount} products.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, int> old category id => new id
     */
    protected function importCategories(string $path): array
    {
        $map = [];
        $pendingParents = [];

        foreach ($this->rows($path) as $row) {
            $category = ProductCategory::create([
                'title' => $row['title'],
                'description' => $this->nullable($row['description'] ?? null),
                'is_active' => $this->bool($row['is_active'] ?? '1'),
            ]);

            $map[(int) $row['id']] = $category->id;

            if ($parentId = $this->nullable($row['parent_id'] ?? null)) {
                $pendingParents[$category->id] = (int) $parentId;
            }
        }

        foreach ($pendingParents as $newId => $oldParentId) {
            if (isset($map[$oldParentId])) {
                ProductCategory::whereKey($newId)->update(['parent_id' => $map[$oldParentId]]);
            }
        }

        return $map;
    }

    /**
     * @return array<int, int> old brand id => new id
     */
    protected function importBrands(string $path): array
    {
        $map = [];

        foreach ($this->rows($path) as $row) {
            $brand = ProductBrand::create([
                'title' => $row['title'],
                'en_title' => $this->nullable($row['en_title'] ?? null),
                'description' => $this->nullable($row['description'] ?? null),
                'is_active' => $this->bool($row['is_active'] ?? '1'),
            ]);

            $map[(int) $row['id']] = $brand->id;
        }

        return $map;
    }

    /**
     * @param  array<int, int>  $categoryMap
     * @param  array<int, int>  $brandMap
     */
    protected function importProducts(string $path, array $categoryMap, array $brandMap): int
    {
        $count = 0;

        DB::transaction(function () use ($path, $categoryMap, $brandMap, &$count) {
            foreach ($this->rows($path) as $row) {
                $sku = $this->nullable($row['sku'] ?? null);

                if (! $sku) {
                    continue;
                }

                $oldCategoryId = $this->nullable($row['category_id'] ?? null);
                $oldBrandId = $this->nullable($row['brand_id'] ?? null);

                Product::create([
                    'sku' => $sku,
                    'barcode' => $this->nullable($row['barcode'] ?? null),
                    'title' => $row['title'],
                    'subtitle' => $this->nullable($row['subtitle'] ?? null),
                    'category_id' => $oldCategoryId ? ($categoryMap[(int) $oldCategoryId] ?? null) : null,
                    'brand_id' => $oldBrandId ? ($brandMap[(int) $oldBrandId] ?? null) : null,
                    'unit' => $this->nullable($row['unit'] ?? null),
                    'package_quantity' => $this->nullableInt($row['package_quantity'] ?? null),
                    'price' => $this->nullableFloat($row['price'] ?? null),
                    'description' => $this->nullable($row['description'] ?? null) ?? $this->nullable($row['short_description'] ?? null),
                    'is_active' => $this->bool($row['is_active'] ?? '1'),
                ]);

                $count++;
            }
        });

        return $count;
    }

    /**
     * @return Generator<int, array<string, string>>
     */
    protected function rows(string $path): Generator
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '\\');

        $headers = null;

        foreach ($file as $line) {
            if (! is_array($line) || $line === [null]) {
                continue;
            }

            if ($headers === null) {
                $headers = $line;

                continue;
            }

            yield array_combine($headers, array_pad($line, count($headers), null));
        }
    }

    protected function nullable(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return ($value === '' || strtoupper($value) === 'NULL') ? null : $value;
    }

    protected function nullableInt(?string $value): ?int
    {
        $value = $this->nullable($value);

        return $value === null ? null : (int) $value;
    }

    protected function nullableFloat(?string $value): ?float
    {
        $value = $this->nullable($value);

        return $value === null ? null : (float) $value;
    }

    protected function bool(string $value): bool
    {
        return (bool) ((int) $value);
    }
}
