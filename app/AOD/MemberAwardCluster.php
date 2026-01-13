<?php

namespace App\AOD;

use App\AOD\Traits\GeneratesAwardImages;
use App\Models\Member;

class MemberAwardCluster
{
    use GeneratesAwardImages;

    private const COLUMNS = 3;

    private const PADDING = 10;

    private const MAX_AWARDS = 6;

    public function generateClusterImage(Member $member): string
    {
        $awards = $this->fetchAwardImages($member);

        if (empty($awards)) {
            return $this->getEmptyAwardsFallback();
        }

        $rows = (int) ceil(count($awards) / self::COLUMNS);
        $baseWidth = (self::COLUMNS * $this->awardSize) + ((self::COLUMNS - 1) * self::PADDING);
        $baseHeight = ($rows * $this->awardSize) + (($rows - 1) * self::PADDING);

        $canvas = $this->createTransparentCanvas($baseWidth, $baseHeight);
        $this->placeAwards($canvas, $awards);

        return $this->renderToPng($canvas);
    }

    private function fetchAwardImages(Member $member): array
    {
        return $this->fetchMemberAwardsCollapseTiered($member, ['awards.image'])
            ->take(self::MAX_AWARDS)
            ->pluck('image')
            ->toArray();
    }

    private function placeAwards(\GdImage $canvas, array $awards): void
    {
        $x = 0;
        $y = 0;

        foreach ($awards as $index => $imagePath) {
            $this->placeAwardOnCanvas($canvas, $imagePath, $x, $y);

            $x += $this->awardSize + self::PADDING;

            if (($index + 1) % self::COLUMNS === 0) {
                $x = 0;
                $y += $this->awardSize + self::PADDING;
            }
        }
    }
}
