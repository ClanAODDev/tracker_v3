<?php

namespace App\AOD;

use App\AOD\Traits\GeneratesAwardImages;
use App\Models\Member;
use App\Models\MemberAward;

class MemberAwardCluster
{
    use GeneratesAwardImages;

    private const AWARD_SIZE = 60;

    private const COLUMNS = 3;

    private const PADDING = 10;

    private const MAX_AWARDS = 6;

    public function generateClusterImage(Member $member): string
    {
        $awards = $this->fetchAwardImages($member);

        if (empty($awards)) {
            return $this->loadFallbackImage(public_path('images/dynamic-images/bgs/no-awards-base-image.png'))
                ?? $this->renderToPng($this->createPlaceholderImage(self::AWARD_SIZE, self::AWARD_SIZE));
        }

        $rows = (int) ceil(count($awards) / self::COLUMNS);
        $baseWidth = (self::COLUMNS * self::AWARD_SIZE) + ((self::COLUMNS - 1) * self::PADDING);
        $baseHeight = ($rows * self::AWARD_SIZE) + (($rows - 1) * self::PADDING);

        $canvas = $this->createTransparentCanvas($baseWidth, $baseHeight);
        $this->placeAwards($canvas, $awards);

        return $this->renderToPng($canvas);
    }

    private function fetchAwardImages(Member $member): array
    {
        return MemberAward::where('member_id', $member->clan_id)
            ->join('awards', 'award_member.award_id', '=', 'awards.id')
            ->where('approved', true)
            ->orderBy('awards.display_order')
            ->take(self::MAX_AWARDS)
            ->pluck('awards.image')
            ->toArray();
    }

    private function placeAwards(\GdImage $canvas, array $awards): void
    {
        $x = 0;
        $y = 0;

        foreach ($awards as $index => $imagePath) {
            $awardImage = $this->loadAwardImage($imagePath, self::AWARD_SIZE, self::AWARD_SIZE);
            $resized = $this->resizeImage($awardImage, self::AWARD_SIZE, self::AWARD_SIZE);

            imagecopy($canvas, $resized, $x, $y, 0, 0, self::AWARD_SIZE, self::AWARD_SIZE);

            imagedestroy($awardImage);
            imagedestroy($resized);

            $x += self::AWARD_SIZE + self::PADDING;

            if (($index + 1) % self::COLUMNS === 0) {
                $x = 0;
                $y += self::AWARD_SIZE + self::PADDING;
            }
        }
    }
}
