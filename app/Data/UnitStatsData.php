<?php

namespace App\Data;

use App\Models\Division;
use Illuminate\Support\Collection;

readonly class UnitStatsData
{
    public function __construct(
        public int $totalCount,
        public int $onLeaveCount,
        public int $inactiveCount,
        public float $avgTenureYears,
        public int $officerCount,
        public int $memberCount,
        public int $inactivityDays,
        public array $voiceActivityGraph,
    ) {}

    public static function fromMembers(Collection $members, ?Division $division, array $voiceActivityGraph): self
    {
        $division          = $division ?? $members->first()?->division;
        $maxDays           = $division?->settings()->get('inactivity_days') ?? 90;
        $now               = now();
        $inactiveThreshold = $now->copy()->subDays($maxDays);

        $totalCount    = $members->count();
        $onLeaveCount  = $members->filter(fn ($m) => $m->leave)->count();
        $activeMembers = $members->reject(fn ($m) => $m->leave);
        $inactiveCount = $activeMembers->filter(
            fn ($m) => $m->last_voice_activity && $m->last_voice_activity < $inactiveThreshold
        )->count();

        $avgTenureDays = $members->avg(fn ($m) => $m->join_date ? $m->join_date->diffInDays($now) : 0);
        $officerCount  = $members->filter(fn ($m) => $m->rank->isOfficer())->count();

        return new self(
            totalCount: $totalCount,
            onLeaveCount: $onLeaveCount,
            inactiveCount: $inactiveCount,
            avgTenureYears: round($avgTenureDays / 365, 1),
            officerCount: $officerCount,
            memberCount: $totalCount - $officerCount,
            inactivityDays: $maxDays,
            voiceActivityGraph: $voiceActivityGraph,
        );
    }
}
