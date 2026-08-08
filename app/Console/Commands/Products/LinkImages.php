<?php

namespace App\Console\Commands\Products;

use App\Models\Products\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('products:link-images {disk-path=products : Directory under the public disk containing images named by SKU}')]
#[Description('Link already-uploaded product images (named by SKU) to their matching products.')]
class LinkImages extends Command
{
    public function handle(): int
    {
        $directory = $this->argument('disk-path');
        $files = Storage::disk('public')->files($directory);

        $linked = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $sku = pathinfo($file, PATHINFO_FILENAME);
            $product = Product::where('sku', $sku)->first();

            if (! $product) {
                $skipped++;

                continue;
            }

            $product->update(['image_path' => $file]);
            $linked++;
        }

        $this->info("Linked {$linked} product image(s). Skipped {$skipped} file(s) with no matching SKU.");

        return self::SUCCESS;
    }
}
