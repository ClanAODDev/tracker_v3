<?php

use App\Models\Member;

Route::get('members/{member}/my-awards.png', function (Member $member) {
    try {

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-Type: image/png');

        $fonts = [
            'tiny' => public_path('fonts/copy0855.ttf'),
            'tinyBold' => public_path('fonts/copy0866.ttf'),
            'big' => public_path('fonts/din-black.otf'),
        ];

        $baseImagePath = public_path('images/dynamic-images/bgs/awards_base_image.png');
        abort_unless(file_exists($baseImagePath), 404, 'Base image not found.');

        $baseImage = imagecreatefrompng($baseImagePath);
        imagesavealpha($baseImage, true);

        $awards = \App\Models\MemberAward::where('member_id', $member->clan_id)
            ->orderByDesc('created_at')
            ->get()
            ->pluck('award.image', 'award.name')
            ->toArray();

        $awardCount = request()->get('award_count', 4);
        $awardCount = min(max((int) $awardCount, 1), 4);

        if (count($awards) < $awardCount) {
            gracefulFail();
        }

        $selectedFiles = collect($awards)
            ->take($awardCount)
            ->map(fn ($path, $name) => [
                'path' => Storage::path('public/' . $path),
                'name' => $name,
            ])
            ->values()
            ->toArray();

        $imageWidth = 60;
        $imageHeight = 60;
        $baseWidth = imagesx($baseImage);
        $baseHeight = imagesy($baseImage);

        $textOffset = filter_var(request('text-offset'), FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 45]]) ?: 20;

        $imageVerticalShift = filter_var(request('image-offset'), FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 45]]) ?: 20;

        $fontType = in_array(request('font'), ['ttf', 'bitmap'], true)
            ? request('font')
            : 'ttf';

        $spacing = ($baseWidth - ($awardCount * $imageWidth)) / ($awardCount + 1);
        $x = $spacing;

        $maxTextWidth = filter_var(request('text-width'), FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]) ?: 100;

        foreach ($selectedFiles as $fileData) {
            $x = placeImageAndText(
                $baseImage,
                $fileData,
                $x,
                $imageWidth,
                $imageHeight,
                $textOffset,
                $baseHeight,
                $fonts,
                $fontType,
                $spacing,
                $maxTextWidth,
                $imageVerticalShift
            );
        }

        imagepng($baseImage);
        imagedestroy($baseImage);
    } catch (\Exception $e) {
        \Log::error($e->getMessage());
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
    $imageVerticalShift
) {
    $filePath = $fileData['path'];
    $awardName = $fileData['name'];

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

    renderText($baseImage, $fonts, $mode, 2, $awardName, $textX, $textY, $textColor, $maxTextWidth);

    imagedestroy($originalImage);
    imagedestroy($resizedImage);

    return $x + $imageWidth + $spacing;
}

function renderText($image, $fonts, $mode, $font, $text, $x, $y, $color, $maxWidth, $fontSize = 8)
{
    if ($mode === 'bitmap') {
        wrapText($image, $font, $text, $x, $y, $color, $maxWidth);
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
        ->orderByDesc('created_at')
        ->get()
        ->take(6)
        ->pluck('award.image')
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
