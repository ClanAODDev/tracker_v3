<?php

namespace App\AOD;

use App\AOD\Traits\GeneratesAwardImages;
use App\Models\Member;
use App\Models\MemberAward;
use GdImage;

class MemberAwardImage
{
    use GeneratesAwardImages;

    private const AWARD_SIZE = 60;

    private const MAX_AWARDS = 4;

    private array $fonts;

    private array $options;

    public function __construct()
    {
        $this->fonts = [
            'tiny' => public_path('fonts/copy0855.ttf'),
            'tinyBold' => public_path('fonts/copy0866.ttf'),
            'big' => public_path('fonts/din-black.otf'),
        ];
    }

    public function generateAwardsImage(Member $member): string
    {
        $baseImagePath = public_path('images/dynamic-images/bgs/awards_base_image.png');
        abort_unless(file_exists($baseImagePath), 404, 'Base image not found.');

        $awardsData = $this->fetchAwardsData($member);

        if (empty($awardsData)) {
            return $this->loadFallbackImage(public_path('images/dynamic-images/bgs/no-awards-base-image.png'))
                ?? $this->renderToPng($this->createPlaceholderImage(self::AWARD_SIZE, self::AWARD_SIZE));
        }

        $baseImage = @imagecreatefrompng($baseImagePath);
        imagesavealpha($baseImage, true);

        $this->options = $this->parseOptions(count($awardsData));
        $awards = array_slice($awardsData, 0, $this->options['count']);

        $this->placeAwards($baseImage, $awards);

        return $this->renderToPng($baseImage);
    }

    private function parseOptions(int $availableCount): array
    {
        $requestedCount = (int) request('award_count', $availableCount);
        $count = min(max($requestedCount, 1), self::MAX_AWARDS, $availableCount);

        $fontType = in_array(request('font'), ['ttf', 'bitmap'], true) ? request('font') : 'ttf';

        return [
            'count' => $count,
            'textOffset' => $this->clampInt(request('text_offset'), 1, 45, 20),
            'imageOffset' => $this->clampInt(request('image_offset'), 1, 45, 20),
            'textWidth' => $this->clampInt(request('text_container_width'), 1, 200, 100),
            'fontType' => $fontType,
            'fontSize' => $this->parseFontSize($fontType),
            'textTransform' => request('text_transform'),
            'showDivision' => (bool) request('division_abbreviation'),
        ];
    }

    private function clampInt($value, int $min, int $max, int $default): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);

        return $int !== false ? max($min, min($max, $int)) : $default;
    }

    private function parseFontSize(string $fontType): int
    {
        $ranges = $fontType === 'ttf' ? [7, 12, 8] : [1, 5, 1];

        return $this->clampInt(request('font_size'), $ranges[0], $ranges[1], $ranges[2]);
    }

    private function fetchAwardsData(Member $member): array
    {
        return MemberAward::where('member_id', $member->clan_id)
            ->join('awards', 'award_member.award_id', '=', 'awards.id')
            ->leftJoin('divisions', 'awards.division_id', '=', 'divisions.id')
            ->where('approved', true)
            ->orderBy('awards.display_order')
            ->get(['awards.image', 'awards.name', 'divisions.abbreviation'])
            ->map(fn ($item) => [
                'image' => $item->image,
                'name' => $item->name,
                'division' => $item->abbreviation ? strtoupper($item->abbreviation) : null,
            ])
            ->toArray();
    }

    private function placeAwards(GdImage $canvas, array $awards): void
    {
        $baseWidth = imagesx($canvas);
        $baseHeight = imagesy($canvas);
        $count = count($awards);

        $spacing = ($baseWidth - ($count * self::AWARD_SIZE)) / ($count + 1);
        $x = $spacing;

        foreach ($awards as $award) {
            $x = $this->placeAward($canvas, $award, $x, $baseHeight, $spacing);
        }
    }

    private function placeAward(GdImage $canvas, array $award, float $x, int $baseHeight, float $spacing): float
    {
        $awardImage = $this->loadAwardImage($award['image'], self::AWARD_SIZE, self::AWARD_SIZE);
        $resized = $this->resizeImage($awardImage, self::AWARD_SIZE, self::AWARD_SIZE);

        $y = ($baseHeight - self::AWARD_SIZE) / 2 - $this->options['imageOffset'];
        imagecopy($canvas, $resized, (int) $x, (int) $y, 0, 0, self::AWARD_SIZE, self::AWARD_SIZE);

        imagedestroy($awardImage);
        imagedestroy($resized);

        $this->renderLabel($canvas, $award, $x, $y);

        return $x + self::AWARD_SIZE + $spacing;
    }

    private function renderLabel(GdImage $canvas, array $award, float $x, float $y): void
    {
        $textColor = imagecolorallocate($canvas, 255, 255, 255);
        $textY = $y + self::AWARD_SIZE + $this->options['textOffset'];
        $textX = $x + (self::AWARD_SIZE / 2) - ($this->options['textWidth'] / 2);

        $label = $this->options['showDivision'] && $award['division']
            ? sprintf('[%s] %s', $award['division'], $award['name'])
            : $award['name'];

        if ($this->options['textTransform'] === 'upper') {
            $label = strtoupper($label);
        }

        if ($this->options['fontType'] === 'bitmap') {
            $this->wrapBitmapText($canvas, $label, $textX, $textY, $textColor);
        } else {
            $this->wrapTtfText($canvas, $label, $textX, $textY, $textColor);
        }
    }

    private function wrapBitmapText(GdImage $image, string $text, float $x, float $y, int $color): void
    {
        $font = $this->options['fontSize'];
        $charWidth = imagefontwidth($font);
        $maxWidth = $this->options['textWidth'];

        $lines = $this->wrapText($text, fn ($line) => strlen($line) * $charWidth, $maxWidth);

        foreach ($lines as $line) {
            $lineWidth = strlen($line) * $charWidth;
            $centeredX = $x + ($maxWidth / 2) - ($lineWidth / 2);
            imagestring($image, $font, (int) $centeredX, (int) $y, $line, $color);
            $y += imagefontheight($font);
        }
    }

    private function wrapTtfText(GdImage $image, string $text, float $x, float $y, int $color): void
    {
        $fontSize = $this->options['fontSize'];
        $fontPath = $this->fonts['tiny'];
        $maxWidth = $this->options['textWidth'];

        $measureWidth = fn ($line) => abs(imagettfbbox($fontSize, 0, $fontPath, $line)[2] ?? 0);
        $lines = $this->wrapText($text, $measureWidth, $maxWidth);

        foreach ($lines as $line) {
            $box = imagettfbbox($fontSize, 0, $fontPath, $line);
            $lineWidth = abs($box[2] - $box[0]);
            $centeredX = $x + ($maxWidth / 2) - ($lineWidth / 2);

            imagettftext($image, $fontSize, 0, (int) $centeredX, (int) $y, $color, $fontPath, $line);
            $y += abs($box[1] - $box[7]) + 5;
        }
    }

    private function wrapText(string $text, callable $measureWidth, int $maxWidth): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine ? "$currentLine $word" : $word;

            if ($measureWidth($testLine) <= $maxWidth) {
                $currentLine = $testLine;
            } else {
                if ($currentLine) {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        return $lines;
    }
}
