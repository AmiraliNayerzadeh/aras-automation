<?php

namespace App\Exports\Attendance;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Square thumbnails of employee photos for embedding in a workbook. Originals
 * can be several megabytes each, which would bloat a report for a whole company
 * into a file too big to mail. The files live until the export object goes away.
 */
class AttendancePhotos
{
    private const SIZE = 160;

    /** @var array<int, ?string> */
    private array $paths = [];

    public function for(User $user): ?string
    {
        return $this->paths[$user->id] ??= $this->makeThumbnail($this->source($user));
    }

    public function __destruct()
    {
        foreach (array_filter($this->paths) as $path) {
            @unlink($path);
        }
    }

    private function source(User $user): ?string
    {
        $disk = Storage::disk('public');

        if ($user->profile_photo_path && $disk->exists($user->profile_photo_path)) {
            return $disk->path($user->profile_photo_path);
        }

        $default = public_path('assets/user-default.jpg');

        return file_exists($default) ? $default : null;
    }

    private function makeThumbnail(?string $source): ?string
    {
        $image = $source ? @imagecreatefromstring((string) file_get_contents($source)) : false;

        if (! $image) {
            return null;
        }

        $side = min(imagesx($image), imagesy($image));
        $thumbnail = imagecreatetruecolor(self::SIZE, self::SIZE);

        imagecopyresampled(
            $thumbnail, $image, 0, 0,
            (int) ((imagesx($image) - $side) / 2), (int) ((imagesy($image) - $side) / 2),
            self::SIZE, self::SIZE, $side, $side,
        );

        $path = tempnam(sys_get_temp_dir(), 'attendance-photo-').'.jpg';

        return imagejpeg($thumbnail, $path, 85) ? $path : null;
    }
}
