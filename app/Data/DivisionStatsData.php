<?php

namespace App\Data;

use App\Models\Census;
use App\Models\Division;

readonly class DivisionStatsData
{
    public function __construct(
        public int $memberCount,
        public int $voiceActiveCount,
        public int $voiceRate,
        public int $recruitsLast30Days,
        public int $activityThresholdDays,
    ) {}

    public static function fromDivision(Division $division, ?Census $latestCensus): self
    {
        $activityThresholdDays = $division->settings()->get('inactivity_days') ?? 30;

        return new self(
            memberCount: $division->members()->count(),
            voiceActiveCount: $latestCensus?->weekly_voice_count ?? 0,
            voiceRate: $latestCensus && $latestCensus->count > 0
                ? (int) round(($latestCensus->weekly_voice_count / $latestCensus->count) * 100)
                : 0,
            recruitsLast30Days: $division->members()
                ->where('join_date', '>=', now()->subDays(30))
                ->count(),
            activityThresholdDays: $activityThresholdDays,
        );
    }
}
