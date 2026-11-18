<?php

namespace App\Console\Commands\Merchandising;

use App\Services\Merchandising\ImportRows;
use App\Services\Merchandising\ProductCatalogImporter;
use App\Services\Merchandising\StoreImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

#[Signature('merchandising:import {type : products or stores} {files* : One or more CSV/XLSX files, imported in order}')]
#[Description('Create or update products (ERP item master) or stores (template, visitor-app export or ERP chart of accounts). Safe to repeat.')]
class ImportMerchandisingData extends Command
{
    public function handle(ProductCatalogImporter $products, StoreImporter $stores): int
    {
        $type = $this->argument('type');

        if (! in_array($type, ['products', 'stores'], true)) {
            $this->error('Type must be "products" or "stores".');

            return self::FAILURE;
        }

        // The ERP product workbook carries a dozen sheets and is large.
        ini_set('memory_limit', '2048M');

        foreach ($this->argument('files') as $path) {
            if (! is_file($path)) {
                $this->error("Not found: {$path}");

                return self::FAILURE;
            }

            $upload = new UploadedFile($path, basename($path), test: true);

            if ($type === 'products') {
                $result = $products->import(ImportRows::fromUpload($upload, ['item_main_group_desc', 'item_code', 'code']));
                $this->info(basename($path).": {$result['created']} created, {$result['updated']} updated, {$result['skipped']} skipped.");
                array_map(fn ($error) => $this->warn('  '.$error), array_slice($result['errors'], 0, 20));

                continue;
            }

            $rows = ImportRows::fromUpload($upload, ['name', 'acc_code', 'food_acc_code']);
            $result = $stores->import($rows);

            if ($result['format'] === 'unknown') {
                $this->error(basename($path).': unrecognised columns.');

                return self::FAILURE;
            }

            $this->info(basename($path)." ({$result['format']}): {$result['created']} created, {$result['updated']} updated, {$result['unchanged']} unchanged, {$result['skipped']} skipped.");
            array_map(fn ($conflict) => $this->warn('  '.$conflict), $result['conflicts']);
        }

        return self::SUCCESS;
    }
}
