<?php

namespace App\AOD\Traits;

use App\Models\Member;
use App\Models\MemberAward;
use GdImage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

trait GeneratesAwardImages
{
    protected int $awardSize = 60;

    protected function getRarityColor(GdImage $canvas, string $rarity): int
    {
        $rgb = config("aod.awards.rarity.{$rarity}.color", config('aod.awards.rarity.common.color'));

        return imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);
    }

    protected function calculateRarity(int $recipientCount): string
    {
        foreach (config('aod.awards.rarity') as $key => $thresholds) {
            if ($recipientCount >= $thresholds['min'] && ($thresholds['max'] === null || $recipientCount <= $thresholds['max'])) {
                return $key;
            }
        }

        return 'common';
    }

    protected function getEmptyAwardsFallback(): string
    {
        return $this->loadFallbackImage(public_path('images/dynamic-images/bgs/no-awards-base-image.png'))
            ?? $this->renderToPng($this->createPlaceholderImage($this->awardSize, $this->awardSize));
    }

    protected function placeAwardOnCanvas(GdImage $canvas, string $imagePath, int $x, int $y): void
    {
        $awardImage = $this->loadAwardImage($imagePath, $this->awardSize, $this->awardSize);
        $resized    = $this->resizeImage($awardImage, $this->awardSize, $this->awardSize);

        imagecopy($canvas, $resized, $x, $y, 0, 0, $this->awardSize, $this->awardSize);

        imagedestroy($awardImage);
        imagedestroy($resized);
    }

    protected function fetchMemberAwardsCollapseTiered(Member $member, array $columns = ['awards.id', 'awards.image', 'awards.prerequisite_award_id']): Collection
    {
        $memberAwards = MemberAward::where('member_id', $member->clan_id)
            ->join('awards', 'award_member.award_id', '=', 'awards.id')
            ->leftJoin('divisions', 'awards.division_id', '=', 'divisions.id')
            ->where('approved', true)
            ->orderBy('awards.display_order')
            ->get(array_unique(array_merge($columns, ['awards.id', 'awards.prerequisite_award_id'])));

        $memberAwardIds = $memberAwards->pluck('id')->unique();
        $skipIds        = collect();

        foreach ($memberAwards as $item) {
            if ($item->prerequisite_award_id && $memberAwardIds->contains($item->prerequisite_award_id)) {
                $skipIds->push($item->prerequisite_award_id);
            }
        }

        return $memberAwards->reject(fn ($item) => $skipIds->contains($item->id));
    }

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

        $textColor  = imagecolorallocate($image, 255, 255, 255);
        $charWidth  = imagefontwidth(5);
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
