<?php

use App\Models\Member;
use Illuminate\Support\Facades\Storage;

Route::get('members/{member}/my-awards.png', function (Member $member) {
    try {

        if (! request('debug')) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header('Content-Type: image/png');
        }

        $fonts = [
            'tiny' => public_path('fonts/copy0855.ttf'),
            'tinyBold' => public_path('fonts/copy0866.ttf'),
            'big' => public_path('fonts/din-black.otf'),
        ];

        $baseImagePath = public_path('images/dynamic-images/bgs/awards_base_image.png');
        abort_unless(file_exists($baseImagePath), 404, 'Base image not found.');

        $baseImage = imagecreatefrompng($baseImagePath);
        imagesavealpha($baseImage, true);

        $awardsData = \App\Models\MemberAward::where('member_id', $member->clan_id)
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

        $awardCount = request()->get('award-count', count($awardsData));
        $awardCount = min(max((int) $awardCount, 1), 4);

        if (count($awardsData) < $awardCount) {
            gracefulFail();
        }

        $awards = array_slice($awardsData, 0, $awardCount);

        $imageWidth = 60;
        $imageHeight = 60;
        $baseWidth = imagesx($baseImage);
        $baseHeight = imagesy($baseImage);

        $text_offset = filter_var(request('text_offset'), FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 45]]) ?: 20;

        $image_offset = filter_var(request('image_offset'), FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 45]]) ?: 20;

        $font = in_array(request('font'), ['ttf', 'bitmap'], true)
            ? request('font')
            : 'ttf';

        $font_size = filter_var(request('font_size'), FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => request('font') === 'ttf' ? 7 : 1,
                'max_range' => request('font') === 'ttf' ? 12 : 5,
            ],
        ]) ?: (request('font') === 'ttf' ? 10 : (request('font') === 'bitmap' ? 1 : 8));

        $text_container_width = filter_var(request('text_container_width'), FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]) ?: 100;

        $spacing = ($baseWidth - ($awardCount * $imageWidth)) / ($awardCount + 1);
        $x = $spacing;

        if (request('debug')) {
            dd(compact('font_size', 'font', 'text_container_width', 'image_offset', 'text_offset', 'spacing'));
        }

        foreach ($awards as $fileData) {

            $x = placeImageAndText(
                $baseImage,
                $fileData,
                $x,
                $imageWidth,
                $imageHeight,
                $text_offset,
                $baseHeight,
                $fonts,
                $font,
                $spacing,
                $text_container_width,
                $image_offset,
                $font_size
            );
        }

        imagepng($baseImage);
        imagedestroy($baseImage);
    } catch (\Exception $e) {
        \Log::error($e->getMessage(), [$e->getLine(), $e->getFile()]);
        gracefulFail();
    }
});

function placeImageAndText(
    $baseImage,
    $fileData,
    $x,
    $imageWidth,
    $imageHeight,
    $textOffset,
    $baseHeight,
    $fonts,
    $mode,
    $spacing,
    $maxTextWidth,
    $imageVerticalShift,
    $fontSize
) {
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

    renderText($baseImage, $fonts, $mode, $label, $textX, $textY, $textColor, $maxTextWidth, $fontSize);

    imagedestroy($originalImage);
    imagedestroy($resizedImage);

    return $x + $imageWidth + $spacing;
}

function renderText($image, $fonts, $mode, $text, $x, $y, $color, $maxWidth, $fontSize)
{
    $text = request('text_transform') === 'upper'
        ? strtoupper($text)
        : $text;

    if ($mode === 'bitmap') {
        wrapText($image, $fontSize, $text, $x, $y, $color, $maxWidth);
    } elseif ($mode === 'ttf') {
        wrapTextTtf($image, $fonts['tiny'], $text, $x, $y, $color, $maxWidth, $fontSize);
    }
}

function wrapTextTtf($image, $fontPath, $text, $x, $y, $color, $maxWidth, $fontSize)
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

function wrapText($image, $font, $text, $x, $y, $color, $maxWidth)
{
    $charWidth = imagefontwidth($font);
    $maxCharsPerLine = floor($maxWidth / $charWidth);

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

function gracefulFail()
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

Route::get('members/{member}/my-awards-cluster.png', function (Member $member) {
    $awards = \App\Models\MemberAward::where('member_id', $member->clan_id)
        ->join('awards', 'award_member.award_id', '=', 'awards.id')
        ->orderBy('awards.display_order')
        ->select('awards.image')
        ->take(6)
        ->get()
        ->pluck('image')
        ->toArray();

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

    header('Content-Type: image/png');
    imagepng($baseImage);
    imagedestroy($baseImage);

});
