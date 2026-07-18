<?php

namespace Database\Seeders;

use App\Models\ClanSnapshot;
use Illuminate\Database\Seeder;

class ClanSnapshotSeeder extends Seeder
{
    public function run(): void
    {
        ClanSnapshot::truncate();

        $weeks        = 12;
        $baseMembers  = 1800;
        $baseVoice    = 420;
        $baseActive   = 900;
        $baseRecruits = 35;

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $date = now()->subWeeks($i)->toDateString();

            $drift              = ($weeks - $i) / $weeks;
            $totalMembers       = (int) round($baseMembers + ($drift * 120) + rand(-15, 15));
            $weeklyVoice        = (int) round($baseVoice + ($drift * 40) + rand(-20, 20));
            $weeklyActive       = (int) round($baseActive + ($drift * 60) + rand(-30, 30));
            $monthlyRecruits    = (int) round($baseRecruits + rand(-8, 12));
            $voiceParticipation = $totalMembers > 0
                ? round(($weeklyVoice / $totalMembers) * 100, 2)
                : 0;

            ClanSnapshot::create([
                'total_members'       => $totalMembers,
                'active_divisions'    => rand(12, 16),
                'weekly_active_count' => $weeklyActive,
                'weekly_voice_count'  => $weeklyVoice,
                'monthly_recruits'    => $monthlyRecruits,
                'voice_participation' => $voiceParticipation,
                'snapshot_date'       => $date,
                'created_at'          => now()->subWeeks($i),
            ]);
        }
    }
}
