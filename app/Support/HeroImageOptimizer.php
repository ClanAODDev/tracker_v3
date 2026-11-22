<?php

namespace App\Support;

use GdImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class HeroImageOptimizer
{
    public const MAX_WIDTH = 2560;

    public const QUALITY = 80;

    private const BACKGROUND = [12, 12, 12];

    private const ROTATIONS = [3 => 180, 6 => -90, 8 => 90];

    public static function store(string $contents, string $directory, string $disk = 'public'): string
    {
        $path = trim($directory, '/') . '/' . Str::random(40) . '.jpg';

        Storage::disk($disk)->put($path, self::optimize($contents));

        return $path;
    }

    public static function optimize(string $contents): string
    {
        $image = imagecreatefromstring($contents);

        if (! $image instanceof GdImage) {
            throw new InvalidArgumentException('The header image could not be read.');
        }

        $image = self::scaleDown(self::orient($image, $contents));

        return self::encode($image);
    }

    private static function orient(GdImage $image, string $contents): GdImage
    {
        if (! function_exists('exif_read_data') || ! str_starts_with($contents, "\xFF\xD8")) {
            return $image;
        }

        $exif        = @exif_read_data('data://image/jpeg;base64,' . base64_encode($contents));
        $orientation = (int) ($exif['Orientation'] ?? 1);

        return isset(self::ROTATIONS[$orientation])
            ? imagerotate($image, self::ROTATIONS[$orientation], 0)
            : $image;
    }

    private static function scaleDown(GdImage $image): GdImage
    {
        if (imagesx($image) <= self::MAX_WIDTH) {
            return $image;
        }

        return imagescale($image, self::MAX_WIDTH, -1, IMG_BICUBIC);
    }

    private static function encode(GdImage $image): string
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);

        imagefill($canvas, 0, 0, imagecolorallocate($canvas, ...self::BACKGROUND));
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imageinterlace($canvas, true);

        ob_start();
        imagejpeg($canvas, null, self::QUALITY);

        return (string) ob_get_clean();
    }
}
