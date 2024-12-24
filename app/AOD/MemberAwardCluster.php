<?php

namespace App\AOD;

use App\Models\Member;
use Illuminate\Support\Facades\Storage;

class MemberAwardCluster
{
    public function generateClusterImage(Member $member)
    {
        $awards = \App\Models\MemberAward::where('member_id', $member->clan_id)
            ->join('awards', 'award_member.award_id', '=', 'awards.id')
            ->orderBy('awards.display_order')
            ->select('awards.image')
            ->take(6)
            ->get()
            ->pluck('image')
            ->toArray();

        // Define image dimensions and layout
        $awardWidth = 60;
        $awardHeight = 60;
        $columns = 3;
        $rows = ceil(count($awards) / $columns);
        $padding = 10;

        $baseWidth = ($columns * $awardWidth) + (($columns - 1) * $padding);
        $baseHeight = ($rows * $awardHeight) + (($rows - 1) * $padding);

        // Create base image with transparency
        $baseImage = imagecreatetruecolor($baseWidth, $baseHeight);
        imagesavealpha($baseImage, true);
        $transparentColor = imagecolorallocatealpha($baseImage, 0, 0, 0, 127);
        imagefill($baseImage, 0, 0, $transparentColor);

        // Place award images
        $this->placeAwardsOnImage($baseImage, $awards, $awardWidth, $awardHeight, $columns, $padding);

        // Return the final image content
        ob_start();
        imagepng($baseImage);
        imagedestroy($baseImage);

        return ob_get_clean();
    }

    protected function placeAwardsOnImage($baseImage, $awards, $awardWidth, $awardHeight, $columns, $padding)
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
