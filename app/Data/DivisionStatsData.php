<?php

namespace App\Data;

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

    public static function fromDivision(Division $division): self
    {
        $activityThresholdDays = $division->settings()->get('inactivity_days') ?? 30;

        $memberCount     = $division->members()->count();
        $voiceActiveCount = $division->membersActiveOnDiscordSinceDaysAgo($activityThresholdDays)->count();

        return new self(
            memberCount: $memberCount,
            voiceActiveCount: $voiceActiveCount,
            voiceRate: $memberCount > 0
                ? (int) round(($voiceActiveCount / $memberCount) * 100)
                : 0,
            recruitsLast30Days: $division->members()
                ->where('join_date', '>=', now()->subDays(30))
                ->count(),
            activityThresholdDays: $activityThresholdDays,
        );
    }
}
