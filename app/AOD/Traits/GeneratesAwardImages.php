<?php

namespace App\AOD\Traits;

use GdImage;
use Illuminate\Support\Facades\Storage;

trait GeneratesAwardImages
{
    protected function loadAwardImage(string $imagePath, int $width, int $height): GdImage
    {
        $filePath = Storage::path('public/' . $imagePath);

        if (file_exists($filePath)) {
            $image = @imagecreatefrompng($filePath);
            if ($image !== false) {
                return $image;
            }
        }

        return $this->createPlaceholderImage($width, $height);
    }

    protected function createPlaceholderImage(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, true);

        $r = mt_rand(50, 200);
        $g = mt_rand(50, 200);
        $b = mt_rand(50, 200);

        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);

        $borderColor = imagecolorallocate($image, min($r + 40, 255), min($g + 40, 255), min($b + 40, 255));
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);
        imagerectangle($image, 1, 1, $width - 2, $height - 2, $borderColor);

        $textColor = imagecolorallocate($image, 255, 255, 255);
        $charWidth = imagefontwidth(5);
        $charHeight = imagefontheight(5);
        imagestring($image, 5, ($width - $charWidth) / 2, ($height - $charHeight) / 2, '?', $textColor);

        return $image;
    }

    protected function renderToPng(GdImage $image): string
    {
        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }

    protected function loadFallbackImage(string $path): ?string
    {
        if (! file_exists($path)) {
            return null;
        }

        $image = @imagecreatefrompng($path);
        if ($image === false) {
            return null;
        }

        return $this->renderToPng($image);
    }

    protected function createTransparentCanvas(int $width, int $height): GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        return $image;
    }

    protected function resizeImage(GdImage $source, int $width, int $height): GdImage
    {
        $resized = $this->createTransparentCanvas($width, $height);

        imagecopyresampled(
            $resized,
            $source,
            0, 0, 0, 0,
            $width, $height,
            imagesx($source), imagesy($source)
        );

        return $resized;
    }
}
