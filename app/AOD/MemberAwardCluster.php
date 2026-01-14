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

        $svg = $this->generateSvg($awards);

        return $this->convertSvgToPng($svg);
    }

    private function fetchAwardImages(Member $member): array
    {
        return $this->fetchMemberAwardsCollapseTiered($member, ['awards.image'])
            ->pluck('image')
            ->filter(function ($imagePath) {
                return file_exists(storage_path('app/public/' . $imagePath));
            })
            ->take(self::MAX_AWARDS)
            ->values()
            ->toArray();
    }

    private function generateSvg(array $awards): string
    {
        $count = count($awards);
        $rows = (int) ceil($count / self::COLUMNS);
        $awardSize = $this->awardSize;
        $padding = self::PADDING;
        $columns = self::COLUMNS;

        $width = ($columns * $awardSize) + (($columns - 1) * $padding);
        $height = ($rows * $awardSize) + (($rows - 1) * $padding);

        $awardsContent = '';
        $y = 0;

        $awardChunks = array_chunk($awards, $columns);

        foreach ($awardChunks as $rowAwards) {
            $rowCount = count($rowAwards);
            $rowWidth = ($rowCount * $awardSize) + (($rowCount - 1) * $padding);
            $xOffset = ($width - $rowWidth) / 2;

            foreach ($rowAwards as $colIndex => $imagePath) {
                $x = $xOffset + ($colIndex * ($awardSize + $padding));
                $imageBase64 = $this->getAwardImageBase64($imagePath);
                if ($imageBase64) {
                    $awardsContent .= <<<IMAGE
    <image x="{$x}" y="{$y}" width="{$awardSize}" height="{$awardSize}"
           href="data:image/png;base64,{$imageBase64}" />
IMAGE;
                }
            }

            $y += $awardSize + $padding;
        }

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
    <rect width="100%" height="100%" fill="transparent"/>
    {$awardsContent}
</svg>
SVG;
    }

    private function getAwardImageBase64(string $imagePath): string
    {
        $filePath = storage_path('app/public/' . $imagePath);
        if (file_exists($filePath)) {
            return base64_encode(file_get_contents($filePath));
        }
        return '';
    }

    private function convertSvgToPng(string $svg): string
    {
        $process = proc_open(
            'rsvg-convert -f png',
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        fwrite($pipes[0], $svg);
        fclose($pipes[0]);

        $png = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return $png;
    }
}
