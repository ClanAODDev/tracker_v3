<?php

namespace App\Data;

use App\Models\Division;

readonly class DivisionStatsData
{
    public function __construct(
        public int $memberCount,
        public int $voiceActiveCount,
        public int $voiceRate,
        public int $recruitsThisMonth,
        public int $activityThresholdDays,
    ) {}

    public static function fromDivision(Division $division): self
    {
        $activityThresholdDays = $division->settings()->get('inactivity_days') ?? 30;

        $division->loadCount([
            'members',
            'members as voice_active_count'    => fn ($q) => $q->where('last_voice_activity', '>=', now()->subDays($activityThresholdDays)->toDateString()),
            'members as recruits_this_month' => fn ($q) => $q->where('join_date', '>=', now()->startOfMonth()->toDateString()),
        ]);

        $memberCount      = (int) $division->members_count;
        $voiceActiveCount = (int) $division->voice_active_count;

        return new self(
            memberCount: $memberCount,
            voiceActiveCount: $voiceActiveCount,
            voiceRate: $memberCount > 0
                ? (int) round(($voiceActiveCount / $memberCount) * 100)
                : 0,
            recruitsThisMonth: (int) $division->recruits_this_month,
            activityThresholdDays: $activityThresholdDays,
        );
    }
}
