<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\LeaderboardSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class LeaderboardSnapshotSeeder extends Seeder
{
    private const CATEGORIES = ['voice', 'growth', 'recruits'];

    private const WEEKS = 12;

    public function run(): void
    {
        LeaderboardSnapshot::truncate();

        $divisions = Division::active()
            ->withoutFloaters()
            ->withoutBR()
            ->whereHas('members')
            ->get();

        if ($divisions->isEmpty()) {
            $this->command->warn('No active divisions found. Run ClanSeeder first.');

            return;
        }

        $weeklyRankings = $this->buildWeeklyRankings($divisions);

        $rows = [];
        $now  = now();

        foreach ($weeklyRankings as $weekIndex => $weekData) {
            $date         = now()->subWeeks(self::WEEKS - 1 - $weekIndex)->toDateString();
            $previousWeek = $weekIndex > 0 ? $weeklyRankings[$weekIndex - 1] : null;

            foreach (self::CATEGORIES as $category) {
                foreach ($weekData[$category] as $rank => $divisionId) {
                    $previousRank = $previousWeek
                        ? array_search($divisionId, $previousWeek[$category])
                        : null;

                    if ($previousRank !== false && $previousRank !== null) {
                        $previousRank = (int) $previousRank;
                        $rankChange   = $previousRank - $rank;
                    } else {
                        $previousRank = null;
                        $rankChange   = 0;
                    }

                    $rows[] = [
                        'division_id'   => $divisionId,
                        'category'      => $category,
                        'rank'          => $rank,
                        'value'         => $this->generateValue($category, $rank, $divisions->count()),
                        'previous_rank' => $previousRank,
                        'rank_change'   => $rankChange,
                        'trend_data'    => json_encode($this->generateTrend($category)),
                        'snapshot_date' => $date,
                        'created_at'    => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            LeaderboardSnapshot::insert($chunk);
        }
    }

    private function buildWeeklyRankings(Collection $divisions): array
    {
        $ids   = $divisions->pluck('id')->toArray();
        $weeks = [];

        $initial = [];
        foreach (self::CATEGORIES as $category) {
            shuffle($ids);
            $initial[$category] = array_values($ids);
        }

        $weeks[0] = $initial;

        for ($i = 1; $i < self::WEEKS; $i++) {
            $previous = $weeks[$i - 1];
            $current  = [];

            foreach (self::CATEGORIES as $category) {
                $ranked             = $previous[$category];
                $ranked             = $this->applyRankShuffling($ranked);
                $current[$category] = $ranked;
            }

            $weeks[$i] = $current;
        }

        return $weeks;
    }

    private function applyRankShuffling(array $ranked): array
    {
        $count = count($ranked);

        for ($j = 0; $j < max(1, (int) ($count * 0.2)); $j++) {
            $a = rand(0, $count - 1);
            $b = rand(0, $count - 1);

            if ($a !== $b) {
                [$ranked[$a], $ranked[$b]] = [$ranked[$b], $ranked[$a]];
            }
        }

        return array_values($ranked);
    }

    private function generateValue(string $category, int $rank, int $total): float
    {
        $position = 1 - (($rank - 1) / max($total - 1, 1));

        return match ($category) {
            'voice'    => round($position * 70 + rand(0, 10), 1),
            'growth'   => round($position * 12 - 2 + (rand(-10, 10) / 10), 1),
            'recruits' => (int) round($position * 25 + rand(0, 5)),
            default    => 0,
        };
    }

    private function generateTrend(string $category): array
    {
        $points = 8;
        $trend  = [];

        $base = match ($category) {
            'voice'    => rand(20, 60),
            'growth'   => rand(-3, 10),
            'recruits' => rand(2, 20),
            default    => 0,
        };

        for ($i = 0; $i < $points; $i++) {
            $base += rand(-3, 3);
            $trend[] = max(0, $base);
        }

        return $trend;
    }
}
