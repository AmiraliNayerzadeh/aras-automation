<?php

namespace App\Support;

use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class Barcode
{
    public static function svg(string $code, float $widthFactor = 1.5, float $height = 40): string
    {
        $generator = new BarcodeGeneratorSVG();

        return $generator->getBarcode($code, BarcodeGenerator::TYPE_CODE_128, $widthFactor, $height);
    }
}
