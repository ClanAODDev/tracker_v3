<?php

namespace App\Data;

use App\Models\Division;
use App\Models\Member;

readonly class ActivityData
{
    public function __construct(
        public ?int $daysSinceVoice,
        public string $health,
        public float $healthPct,
        public int $divisionMax,
    ) {}

    public static function fromMember(Member $member, ?Division $division): self
    {
        $daysSinceVoice = $member->last_voice_activity
            ? (int) $member->last_voice_activity->diffInDays()
            : null;

        $health = match (true) {
            $daysSinceVoice === null => 'unknown',
            $daysSinceVoice <= 7     => 'excellent',
            $daysSinceVoice <= 14    => 'good',
            $daysSinceVoice <= 30    => 'warning',
            default                  => 'critical',
        };

        $divisionMax = $division?->settings()->get('inactivity_days') ?? 30;
        $healthPct   = $daysSinceVoice !== null
            ? max(0, min(100, 100 - ($daysSinceVoice / $divisionMax * 100)))
            : 0;

        return new self(
            daysSinceVoice: $daysSinceVoice,
            health: $health,
            healthPct: $healthPct,
            divisionMax: $divisionMax,
        );
    }
}
