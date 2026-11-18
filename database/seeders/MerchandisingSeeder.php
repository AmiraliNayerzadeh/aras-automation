<?php

namespace Database\Seeders;

use App\Services\Merchandising\ImportRows;
use App\Services\Merchandising\ProductCatalogImporter;
use App\Services\Merchandising\StoreImporter;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

/**
 * Loads the product catalog (with photos) that ships in the repository, then
 * any store lists dropped into storage/app/private/merchandising-import. The
 * store lists hold customers' contact details, so they're never committed: the
 * repository is public. Safe to run again: everything is matched on codes.
 *
 *   php artisan db:seed --class=MerchandisingSeeder
 */
class MerchandisingSeeder extends Seeder
{
    public const STORE_IMPORT_DIRECTORY = 'app/private/merchandising-import';

    public function run(ProductCatalogImporter $products, StoreImporter $stores): void
    {
        $data = database_path('seeders/data/merchandising');

        $result = $products->import(ImportRows::fromCsv($data.'/products.csv'), $data.'/product-images');
        $this->command?->info("Products: {$result['created']} created, {$result['updated']} updated, {$result['skipped']} skipped.");

        $directory = storage_path(self::STORE_IMPORT_DIRECTORY);
        $files = File::isDirectory($directory) ? collect(File::files($directory))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['csv', 'xlsx', 'xls'], true))
            // Visitor-app exports first: they carry the best names and addresses.
            ->sortBy(fn ($f) => str_ends_with(strtolower($f->getFilename()), '.csv') ? 0 : 1)
            ->values() : collect();

        if ($files->isEmpty()) {
            $this->command?->warn('No store files in storage/'.self::STORE_IMPORT_DIRECTORY.'; skipping stores.');

            return;
        }

        foreach ($files as $file) {
            $upload = new UploadedFile($file->getPathname(), $file->getFilename(), test: true);
            $result = $stores->import(ImportRows::fromUpload($upload, ['name', 'acc_code', 'food_acc_code']));

            $this->command?->info("Stores from {$file->getFilename()} ({$result['format']}): {$result['created']} created, {$result['updated']} updated, {$result['unchanged']} unchanged, {$result['skipped']} skipped, ".count($result['conflicts']).' conflicts.');
        }
    }
}
