<?php

namespace App\Data;

use App\Models\LeaderboardSnapshot;
use Illuminate\Support\Collection;

readonly class LeaderboardTrendData
{
    public function __construct(
        public int $divisionId,
        public string $category,
        public Collection $history,
        public ?int $bestRank,
        public ?int $worstRank,
        public ?float $averageRank,
        public int $longestStreakAtFirst,
        public float $volatility,
    ) {}

    public static function forDivision(int $divisionId, string $category, int $weeks = 12): self
    {
        $snapshots = LeaderboardSnapshot::query()
            ->forDivision($divisionId)
            ->forCategory($category)
            ->recent()
            ->limit($weeks)
            ->get()
            ->reverse()
            ->values();

        $ranks = $snapshots->pluck('rank');

        return new self(
            divisionId: $divisionId,
            category: $category,
            history: $snapshots->map(fn ($s) => [
                'date' => $s->snapshot_date->toDateString(),
                'rank' => $s->rank,
                'value' => (float) $s->value,
            ]),
            bestRank: $ranks->min(),
            worstRank: $ranks->max(),
            averageRank: $ranks->isNotEmpty() ? round($ranks->avg(), 1) : null,
            longestStreakAtFirst: self::calculateStreakAtFirst($ranks),
            volatility: self::calculateVolatility($ranks),
        );
    }

    private static function calculateStreakAtFirst(Collection $ranks): int
    {
        $longest = 0;
        $current = 0;

        foreach ($ranks as $rank) {
            if ($rank === 1) {
                $current++;
                $longest = max($longest, $current);
            } else {
                $current = 0;
            }
        }

        return $longest;
    }

    private static function calculateVolatility(Collection $ranks): float
    {
        if ($ranks->count() < 2) {
            return 0.0;
        }

        $changes = $ranks->sliding(2)->map(fn ($pair) => abs($pair->last() - $pair->first()));

        return round($changes->avg(), 2);
    }
}
