<?php

namespace App\AOD;

use App\Models\Member;
use App\Models\MemberAward;
use Illuminate\Support\Facades\Storage;

class MemberAwardCluster
{
    public function generateClusterImage(Member $member): false|string
    {
        $awards = MemberAward::where('member_id', $member->clan_id)
            ->join('awards', 'award_member.award_id', '=', 'awards.id')
            ->where('approved', true)
            ->orderBy('awards.display_order')
            ->select('awards.image')
            ->take(6)
            ->get()
            ->pluck('image')
            ->toArray();

        if (count($awards) === 0) {
            $noAwardsImage = public_path('images/dynamic-images/bgs/no-awards-base-image.png');
            if (file_exists($noAwardsImage)) {
                $brokenImage = imagecreatefrompng($noAwardsImage);
                header('Content-Type: image/png');
                imagepng($brokenImage);
                imagedestroy($brokenImage);

                exit;
            }
        }

        $awardWidth = 60;
        $awardHeight = 60;
        $columns = 3;
        $rows = ceil(count($awards) / $columns);
        $padding = 10;

        $baseWidth = ($columns * $awardWidth) + (($columns - 1) * $padding);
        $baseHeight = ($rows * $awardHeight) + (($rows - 1) * $padding);

        $baseImage = imagecreatetruecolor($baseWidth, $baseHeight);
        imagesavealpha($baseImage, true);
        $transparentColor = imagecolorallocatealpha($baseImage, 0, 0, 0, 127);
        imagefill($baseImage, 0, 0, $transparentColor);

        $this->placeAwardsOnImage($baseImage, $awards, $awardWidth, $awardHeight, $columns, $padding);

        ob_start();
        imagepng($baseImage);
        imagedestroy($baseImage);

        return ob_get_clean();
    }

    protected function placeAwardsOnImage($baseImage, $awards, $awardWidth, $awardHeight, $columns, $padding): void
    {
        $x = 0;
        $y = 0;
        $counter = 0;

        foreach ($awards as $awardPath) {
            $awardImagePath = Storage::path('public/' . $awardPath);

            if (file_exists($awardImagePath)) {
                $awardImage = @imagecreatefrompng($awardImagePath);

                imagesavealpha($awardImage, true);

                imagecopyresampled(
                    $baseImage,
                    $awardImage,
                    $x,
                    $y,
                    0,
                    0,
                    $awardWidth,
                    $awardHeight,
                    imagesx($awardImage),
                    imagesy($awardImage)
                );

                imagedestroy($awardImage);
            }

            $x += $awardWidth + $padding;
            $counter++;

            if ($counter % $columns == 0) {
                $x = 0;
                $y += $awardHeight + $padding;
            }
        }
    }
}
