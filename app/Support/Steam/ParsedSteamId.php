<?php

namespace App\Support\Steam;

use App\Enums\SteamInputFormat;

final readonly class ParsedSteamId
{
    public function __construct(
        public SteamInputFormat $format,
        public ?string $steamId = null,
        public ?string $vanity = null,
        public ?string $reason = null,
    ) {}

    public function needsLookup(): bool
    {
        return $this->format === SteamInputFormat::VANITY;
    }

    public function isInvalid(): bool
    {
        return $this->format === SteamInputFormat::INVALID;
    }
}
