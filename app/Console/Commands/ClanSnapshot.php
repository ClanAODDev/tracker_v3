<?php

namespace App\Console\Commands;

use App\Models\Census;
use App\Models\ClanSnapshot as Snapshot;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class ClanSnapshot extends BaseCommand
{
    protected $signature = 'tracker:clan-snapshot
                            {--force : Run even if a snapshot already exists for today}';

    protected $description = 'Capture clan-wide aggregate stats for trend tracking';

    public function handle(): int
    {
        $today = today()->toDateString();

        if (! $this->option('force') && Snapshot::where('snapshot_date', $today)->exists()) {
            $this->warn('Clan snapshot already exists for today. Use --force to overwrite.');

            return self::SUCCESS;
        }

        if ($this->option('force')) {
            Snapshot::where('snapshot_date', $today)->delete();
        }

        $activeDivisionIds = Division::active()->whereHas('members')->pluck('id');

        if ($activeDivisionIds->isEmpty()) {
            $this->info('No active divisions found.');

            return self::SUCCESS;
        }

        $latestCensusIds = Census::select(DB::raw('MAX(id) as id'))
            ->whereIn('division_id', $activeDivisionIds)
            ->groupBy('division_id')
            ->pluck('id');

        $aggregates = Census::whereIn('id', $latestCensusIds)
            ->selectRaw('SUM(count) as total_members, SUM(weekly_active_count) as weekly_active, SUM(weekly_voice_count) as weekly_voice')
            ->first();

        $totalMembers = (int) ($aggregates->total_members ?? 0);
        $weeklyActive = (int) ($aggregates->weekly_active ?? 0);
        $weeklyVoice  = (int) ($aggregates->weekly_voice ?? 0);

        $monthlyRecruits    = Member::where('join_date', '>=', now()->startOfMonth())->count();
        $voiceParticipation = $totalMembers > 0 ? round(($weeklyVoice / $totalMembers) * 100, 2) : 0;

        Snapshot::create([
            'total_members'       => $totalMembers,
            'active_divisions'    => $activeDivisionIds->count(),
            'weekly_active_count' => $weeklyActive,
            'weekly_voice_count'  => $weeklyVoice,
            'monthly_recruits'    => $monthlyRecruits,
            'voice_participation' => $voiceParticipation,
            'snapshot_date'       => $today,
            'created_at'          => now(),
        ]);

        return $this->succeedWithMessage(
            "Clan snapshot recorded: {$totalMembers} members across {$activeDivisionIds->count()} divisions, {$voiceParticipation}% voice"
        );
    }
}
