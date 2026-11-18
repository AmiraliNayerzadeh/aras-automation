<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Small JPEG data URIs of stored photos for PDFs: phone photos are several
 * megabytes each, which would make a report with dozens of visits unusable.
 */
class ImageThumbnails
{
    /** @var array<string, ?string> */
    private array $cache = [];

    public function dataUri(?string $publicPath, int $maxSide = 420): ?string
    {
        if (! $publicPath) {
            return null;
        }

        return $this->cache[$publicPath.'@'.$maxSide] ??= $this->make($publicPath, $maxSide);
    }

    private function make(string $publicPath, int $maxSide): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($publicPath) || $disk->size($publicPath) > 40 * 1024 * 1024) {
            return null;
        }

        $image = @imagecreatefromstring((string) $disk->get($publicPath));

        if (! $image) {
            return null;
        }

        $scale = min(1, $maxSide / max(imagesx($image), imagesy($image)));
        $width = max(1, (int) round(imagesx($image) * $scale));
        $height = max(1, (int) round(imagesy($image) * $scale));

        $thumbnail = imagecreatetruecolor($width, $height);
        imagefill($thumbnail, 0, 0, imagecolorallocate($thumbnail, 255, 255, 255));
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($thumbnail, null, 78);

        return 'data:image/jpeg;base64,'.base64_encode((string) ob_get_clean());
    }
}
