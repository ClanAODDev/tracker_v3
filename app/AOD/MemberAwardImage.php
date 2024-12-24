<?php

namespace App\AOD;

use App\Models\Member;
use App\Models\MemberAward;
use Illuminate\Support\Facades\Storage;

class MemberAwardImage
{
    protected array $fonts;

    public function __construct()
    {
        $this->fonts = [
            'tiny' => public_path('fonts/copy0855.ttf'),
            'tinyBold' => public_path('fonts/copy0866.ttf'),
            'big' => public_path('fonts/din-black.otf'),
        ];
    }

    public function generateAwardsImage(Member $member)
    {
        $baseImagePath = public_path('images/dynamic-images/bgs/awards_base_image.png');
        abort_unless(file_exists($baseImagePath), 404, 'Base image not found.');

        $baseImage = imagecreatefrompng($baseImagePath);
        imagesavealpha($baseImage, true);

        $awardsData = $this->fetchAwardsData($member);

        $awardCount = request()->get('award_count', count($awardsData));
        $awardCount = min(max((int) $awardCount, 1), 4);

        if (count($awardsData) < $awardCount) {
            return $this->gracefulFail();
        }

        $awards = array_slice($awardsData, 0, $awardCount);
        $this->placeAwardsOnImage($baseImage, $awards, $awardCount);

        header('Content-Type: image/png');
        imagepng($baseImage);
        imagedestroy($baseImage);
    }

    protected function fetchAwardsData(Member $member)
    {
        return MemberAward::where('member_id', $member->clan_id)
            ->join('awards', 'award_member.award_id', '=', 'awards.id')
            ->leftJoin('divisions', 'awards.division_id', '=', 'divisions.id')
            ->orderBy('awards.display_order')
            ->get([
                'awards.image as award_image',
                'awards.name as award_name',
                'divisions.abbreviation as division',
            ])
            ->map(function ($item) {
                return [
                    'award_image' => $item->award_image,
                    'award_name' => $item->award_name,
                    'division' => $item->division ? strtoupper($item->division) : null,
                ];
            })
            ->toArray();
    }

    protected function placeAwardsOnImage($baseImage, $awards, $awardCount)
    {
        $imageWidth = 60;
        $imageHeight = 60;
        $baseWidth = imagesx($baseImage);
        $baseHeight = imagesy($baseImage);

        $textOffset = $this->filterInt(request('text_offset'), 1, 45, 20);
        $imageOffset = $this->filterInt(request('image_offset'), 1, 45, 20);
        $font = in_array(request('font'), ['ttf', 'bitmap'], true) ? request('font') : 'ttf';
        $fontSize = $this->filterFontSize($font);
        $textContainerWidth = $this->filterInt(request('text_container_width'), 1, null, 100);

        $spacing = ($baseWidth - ($awardCount * $imageWidth)) / ($awardCount + 1);
        $x = $spacing;

        foreach ($awards as $fileData) {
            $x = $this->placeImageAndText(
                $baseImage,
                $fileData,
                $x,
                $imageWidth,
                $imageHeight,
                $textOffset,
                $baseHeight,
                $font,
                $spacing,
                $textContainerWidth,
                $imageOffset,
                $fontSize
            );
        }
    }

    protected function placeImageAndText($baseImage, $fileData, $x, $imageWidth, $imageHeight, $textOffset, $baseHeight, $font, $spacing, $maxTextWidth, $imageVerticalShift, $fontSize)
    {
        $filePath = Storage::path('public/' . $fileData['award_image']);
        $awardName = $fileData['award_name'];
        $division = $fileData['division'];

        $originalImage = imagecreatefrompng($filePath);
        $resizedImage = imagecreatetruecolor($imageWidth, $imageHeight);
        imagesavealpha($resizedImage, true);
        $transparentColor = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
        imagefill($resizedImage, 0, 0, $transparentColor);

        imagecopyresampled(
            $resizedImage,
            $originalImage,
            0, 0,
            0, 0,
            $imageWidth,
            $imageHeight,
            imagesx($originalImage),
            imagesy($originalImage)
        );

        $yCenter = ($baseHeight - $imageHeight) / 2 - $imageVerticalShift;
        imagecopy($baseImage, $resizedImage, $x, $yCenter, 0, 0, $imageWidth, $imageHeight);

        $textColor = imagecolorallocate($baseImage, 255, 255, 255);
        $textY = $yCenter + $imageHeight + $textOffset;

        $textX = $x + ($imageWidth / 2) - ($maxTextWidth / 2);
        $label = $division ? sprintf('[%s] %s', $division, $awardName) : $awardName;

        $this->renderText($baseImage, $font, $label, $textX, $textY, $textColor, $maxTextWidth, $fontSize);

        imagedestroy($originalImage);
        imagedestroy($resizedImage);

        return $x + $imageWidth + $spacing;
    }

    protected function renderText($image, $mode, $text, $x, $y, $color, $maxWidth, $fontSize)
    {
        $text = request('text_transform') === 'upper' ? strtoupper($text) : $text;

        if ($mode === 'bitmap') {
            $this->wrapText($image, $fontSize, $text, $x, $y, $color, $maxWidth);
        } elseif ($mode === 'ttf') {
            $this->wrapTextTtf($image, $this->fonts['tiny'], $text, $x, $y, $color, $maxWidth, $fontSize);
        }
    }

    protected function filterInt($value, $min, $max, $default)
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => compact('min', 'max')]) ?: $default;
    }

    protected function filterFontSize($font)
    {
        return filter_var(request('font_size'), FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => $font === 'ttf' ? 7 : 1,
                'max_range' => $font === 'ttf' ? 12 : 5,
            ],
        ]) ?: ($font === 'ttf' ? 8 : 1);
    }

    protected function gracefulFail()
    {
        $brokenImagePath = public_path('images/dynamic-images/bgs/awards_broke_image.png');
        if (file_exists($brokenImagePath)) {
            $brokenImage = imagecreatefrompng($brokenImagePath);
            header('Content-Type: image/png');
            imagepng($brokenImage);
            imagedestroy($brokenImage);
        } else {
            abort(500, 'Broken image not found.');
        }
    }

    protected function wrapText($image, $font, $text, $x, $y, $color, $maxWidth)
    {
        $charWidth = imagefontwidth($font);

        $lines = [];
        $words = explode(' ', $text);
        $currentLine = '';

        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) * $charWidth <= $maxWidth) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        foreach ($lines as $line) {
            $lineWidth = strlen($line) * $charWidth;
            $centeredX = $x + ($maxWidth / 2) - ($lineWidth / 2);
            imagestring($image, $font, $centeredX, $y, $line, $color);
            $y += imagefontheight($font);
        }
    }

    protected function wrapTextTtf($image, $fontPath, $text, $x, $y, $color, $maxWidth, $fontSize)
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine ? "$currentLine $word" : $word;
            $testBox = imagettfbbox($fontSize, 0, $fontPath, $testLine);

            $testWidth = abs($testBox[2] - $testBox[0]);

            if ($testWidth <= $maxWidth) {
                $currentLine = $testLine;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        foreach ($lines as $line) {
            $lineBox = imagettfbbox($fontSize, 0, $fontPath, $line);
            $lineWidth = abs($lineBox[2] - $lineBox[0]);

            $centeredX = $x + ($maxWidth / 2) - ($lineWidth / 2);

            imagettftext($image, $fontSize, 0, $centeredX, $y, $color, $fontPath, $line);

            $y += abs($lineBox[1] - $lineBox[7]) + 5;
        }
    }
}
