<?php

namespace App\Console\Commands;

use App\Data\DivisionLeaderboardData;
use App\Models\LeaderboardSnapshot as Snapshot;

class LeaderboardSnapshot extends BaseCommand
{
    protected $signature = 'tracker:leaderboard-snapshot
                            {--force : Run even if a snapshot already exists for today}';

    protected $description = 'Capture division leaderboard rankings for trend tracking';

    public function handle(): int
    {
        $today = today()->toDateString();

        if (! $this->option('force') && Snapshot::where('snapshot_date', $today)->exists()) {
            $this->warn('Snapshot already exists for today. Use --force to overwrite.');

            return self::SUCCESS;
        }

        if ($this->option('force')) {
            Snapshot::where('snapshot_date', $today)->delete();
        }

        $leaderboards       = DivisionLeaderboardData::calculate();
        $previousByCategory = $this->getPreviousRanks($today);

        $rows = [];
        $now  = now();

        foreach (['voice' => 'voiceLeaders', 'growth' => 'growthLeaders', 'recruits' => 'recruitLeaders'] as $category => $key) {
            foreach ($leaderboards[$key] as $index => $entry) {
                $rank         = $index + 1;
                $previousRank = $previousByCategory[$category][$entry['id']] ?? null;

                $rows[] = [
                    'division_id'   => $entry['id'],
                    'category'      => $category,
                    'rank'          => $rank,
                    'value'         => $entry['value'],
                    'previous_rank' => $previousRank,
                    'rank_change'   => $previousRank ? $previousRank - $rank : 0,
                    'trend_data'    => json_encode($entry['trend'] ?? []),
                    'snapshot_date' => $today,
                    'created_at'    => $now,
                ];
            }
        }

        Snapshot::insert($rows);
        DivisionLeaderboardData::clearCache();

        return $this->succeedWithMessage('Snapshot recorded: ' . count($rows) . ' entries across 3 categories');
    }

    private function getPreviousRanks(string $excludeDate): array
    {
        $previous = Snapshot::where('snapshot_date', '<', $excludeDate)
            ->orderByDesc('snapshot_date')
            ->limit(1)
            ->value('snapshot_date');

        if (! $previous) {
            return [];
        }

        return Snapshot::where('snapshot_date', $previous)
            ->get()
            ->groupBy('category')
            ->map(fn ($rows) => $rows->pluck('rank', 'division_id')->all())
            ->all();
    }
}
