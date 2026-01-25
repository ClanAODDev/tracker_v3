<?php

namespace App\AOD;

use App\AOD\Traits\GeneratesAwardImages;
use App\Models\Member;

class MemberAwardImage
{
    use GeneratesAwardImages;

    private const MAX_AWARDS = 4;
    private const CANVAS_WIDTH = 500;
    private const CANVAS_HEIGHT = 131;

    private array $fonts;
    private array $options;

    public function __construct()
    {
        $this->fonts = [
            'big' => public_path('fonts/din-black.otf'),
        ];
    }

    public function generateAwardsImage(Member $member): string
    {
        return $this->generateSvgContent($member);
    }

    public function generateAwardsImagePng(Member $member, bool $withBackground = false): string
    {
        $awardsData = $this->fetchAwardsData($member);

        if (empty($awardsData) && $withBackground) {
            return file_get_contents(public_path('images/dynamic-images/bgs/no-awards-base-image.png'));
        }

        $svg = $this->generateSvgContentFromData($awardsData);

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

        if ($withBackground) {
            $png = $this->compositeOnBackground($png);
        }

        return $png;
    }

    private function compositeOnBackground(string $overlayPng): string
    {
        $backgroundPath = public_path('images/dynamic-images/bgs/awards_base_image.png');

        $background = new \Imagick($backgroundPath);
        $overlay = new \Imagick();
        $overlay->readImageBlob($overlayPng);

        $background->compositeImage($overlay, \Imagick::COMPOSITE_OVER, 0, 0);

        $result = $background->getImageBlob();

        $background->destroy();
        $overlay->destroy();

        return $result;
    }

    private function generateSvgContent(Member $member): string
    {
        return $this->generateSvgContentFromData($this->fetchAwardsData($member));
    }

    private function generateSvgContentFromData(array $awardsData): string
    {
        if (empty($awardsData)) {
            return $this->generateEmptySvg();
        }

        $this->options = $this->parseOptions(count($awardsData));
        $awards = array_slice($awardsData, 0, $this->options['count']);

        return $this->generateSvg($awards);
    }

    private function parseOptions(int $availableCount): array
    {
        $requestedCount = (int) request('award_count', $availableCount);
        $count = min(max($requestedCount, 1), self::MAX_AWARDS, $availableCount);

        $defaultFontSize = $count >= 4 ? 10 : 11;

        return [
            'count' => $count,
            'textOffset' => 25,
            'imageOffset' => 20,
            'fontSize' => $this->clampInt(request('font_size'), 8, 14, $defaultFontSize),
            'maxLines' => $count >= 4 ? 2 : 3,
        ];
    }

    private function clampInt($value, int $min, int $max, int $default): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        return $int !== false ? max($min, min($max, $int)) : $default;
    }

    private function fetchAwardsData(Member $member): array
    {
        $memberAwardCounts = \App\Models\MemberAward::where('member_id', $member->clan_id)
            ->where('approved', true)
            ->selectRaw('award_id, COUNT(*) as count')
            ->groupBy('award_id')
            ->pluck('count', 'award_id');

        return $this->fetchMemberAwardsCollapseTiered($member, ['awards.image', 'awards.name', 'divisions.abbreviation'])
            ->map(function ($item) use ($memberAwardCounts) {
                $recipientCount = \App\Models\MemberAward::where('award_id', $item->id)
                    ->where('approved', true)
                    ->whereHas('member', fn ($q) => $q->where('division_id', '>', 0))
                    ->count();

                return [
                    'image' => $item->image,
                    'name' => $item->name,
                    'division' => $item->abbreviation ? strtoupper($item->abbreviation) : null,
                    'rarity' => $this->calculateRarity($recipientCount),
                    'count' => $memberAwardCounts->get($item->id, 1),
                ];
            })
            ->values()
            ->toArray();
    }

    private function generateSvg(array $awards): string
    {
        $count = count($awards);
        $spacing = (self::CANVAS_WIDTH - ($count * $this->awardSize)) / ($count + 1);

        $defs = $this->generateDefs();
        $awardsContent = '';
        $x = $spacing;

        foreach ($awards as $award) {
            $centerX = $x + ($this->awardSize / 2);
            $y = (self::CANVAS_HEIGHT - $this->awardSize) / 2 - $this->options['imageOffset'];
            $awardsContent .= $this->renderAward($award, $x, $y, $centerX);
            $x += $this->awardSize + $spacing;
        }

        $width = self::CANVAS_WIDTH;
        $height = self::CANVAS_HEIGHT;

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
     width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}"
     overflow="visible">
    <defs>
        {$defs}
        <style>
            @font-face {
                font-family: 'DIN Black';
                src: url('data:font/opentype;base64,{$this->getFontBase64()}') format('opentype');
            }
            .award-text {
                font-family: 'DIN Black', Arial Black, sans-serif;
                font-size: {$this->options['fontSize']}px;
                text-anchor: middle;
                font-weight: bold;
                letter-spacing: -0.5px;
            }
            .award-shadow {
                fill: black;
                opacity: 0.8;
            }
            .badge-text {
                font-family: 'DIN Black', Arial Black, sans-serif;
                font-size: 9px;
                fill: white;
                text-anchor: middle;
                dominant-baseline: middle;
            }
        </style>
    </defs>
    <rect width="100%" height="100%" fill="transparent"/>
    {$awardsContent}
</svg>
SVG;
    }

    private function generateDefs(): string
    {
        $filters = '';
        foreach (['mythic', 'legendary', 'epic', 'rare', 'common'] as $rarity) {
            $rgb = config("aod.awards.rarity.{$rarity}.color", [128, 128, 128]);
            $filters .= <<<FILTER
        <filter id="glow-{$rarity}" x="-50%" y="-50%" width="200%" height="200%">
            <feGaussianBlur in="SourceAlpha" stdDeviation="3" result="blur"/>
            <feFlood flood-color="rgb({$rgb[0]},{$rgb[1]},{$rgb[2]})" flood-opacity="0.5"/>
            <feComposite in2="blur" operator="in"/>
            <feMerge>
                <feMergeNode/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
FILTER;
        }

        return <<<DEFS
        <linearGradient id="bgGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:#1a1a2e;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#16213e;stop-opacity:1" />
        </linearGradient>
        {$filters}
DEFS;
    }

    private function renderAward(array $award, float $x, float $y, float $slotCenterX): string
    {
        $rgb = config("aod.awards.rarity.{$award['rarity']}.color", [128, 128, 128]);
        $textColor = "rgb({$rgb[0]},{$rgb[1]},{$rgb[2]})";
        $rarity = $award['rarity'];

        $imageBase64 = $this->getAwardImageBase64($award['image']);
        $centerX = $slotCenterX;
        $textY = $y + $this->awardSize + $this->options['textOffset'];

        $label = strtoupper($award['name']);
        $lines = $this->wrapText($label, 100);
        if (count($lines) > $this->options['maxLines']) {
            $lines = array_slice($lines, 0, $this->options['maxLines']);
            $lines[count($lines) - 1] = rtrim($lines[count($lines) - 1]) . '...';
        }

        $textElements = '';
        foreach ($lines as $i => $line) {
            $lineY = $textY + ($i * ($this->options['fontSize'] + 2));
            $line = htmlspecialchars($line, ENT_XML1);
            $textElements .= <<<TEXT
        <text x="{$centerX}" y="{$lineY}" class="award-text award-shadow" dx="1" dy="1">{$line}</text>
        <text x="{$centerX}" y="{$lineY}" class="award-text" fill="{$textColor}">{$line}</text>
TEXT;
        }

        $badge = '';
        if ($award['count'] > 1) {
            $badgeX = $x + $this->awardSize - 8;
            $badgeY = $y + $this->awardSize - 8;
            $badge = <<<BADGE
        <circle cx="{$badgeX}" cy="{$badgeY}" r="9" fill="rgb(40,40,50)" stroke="gold" stroke-width="1"/>
        <text x="{$badgeX}" y="{$badgeY}" class="badge-text">x{$award['count']}</text>
BADGE;
        }

        return <<<AWARD
    <g class="award-group" filter="url(#glow-{$rarity})">
        <image x="{$x}" y="{$y}" width="{$this->awardSize}" height="{$this->awardSize}"
               xlink:href="data:image/png;base64,{$imageBase64}" />
    </g>
    {$badge}
    {$textElements}
AWARD;
    }

    private function getAwardImageBase64(string $imagePath): string
    {
        $filePath = storage_path('app/public/' . $imagePath);
        if (file_exists($filePath)) {
            return base64_encode(file_get_contents($filePath));
        }
        return '';
    }

    private function getFontBase64(): string
    {
        if (file_exists($this->fonts['big'])) {
            return base64_encode(file_get_contents($this->fonts['big']));
        }
        return '';
    }

    private function wrapText(string $text, int $maxWidth): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        $charWidth = $this->options['fontSize'] * 0.6;

        foreach ($words as $word) {
            $testLine = $currentLine ? "$currentLine $word" : $word;
            $width = strlen($testLine) * $charWidth;

            if ($width <= $maxWidth) {
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

    private function generateEmptySvg(): string
    {
        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{self::CANVAS_WIDTH}" height="{self::CANVAS_HEIGHT}">
    <rect width="100%" height="100%" fill="#1a1a2e"/>
    <text x="50%" y="50%" text-anchor="middle" fill="#666" font-family="Arial" font-size="14">No Awards</text>
</svg>
SVG;
    }
}
