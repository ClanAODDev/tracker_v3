<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tenureAwards = [
        18 => 5,
        19 => 10,
        139 => 15,
        140 => 20,
    ];

    public function up(): void
    {
        $tenureIds = array_keys($this->tenureAwards);

        $membersWithTenure = DB::table('award_member')
            ->whereIn('award_id', $tenureIds)
            ->where('approved', true)
            ->orderBy('member_id')
            ->get()
            ->groupBy('member_id');

        foreach ($membersWithTenure as $memberId => $awards) {
            $highestAward = $awards->sortByDesc(fn ($a) => $this->tenureAwards[$a->award_id])->first();
            $highestYears = $this->tenureAwards[$highestAward->award_id];
            $highestDate = Carbon::parse($highestAward->created_at);

            $existingIds = $awards->pluck('award_id')->toArray();

            foreach ($this->tenureAwards as $awardId => $years) {
                if ($years >= $highestYears) {
                    continue;
                }

                if (in_array($awardId, $existingIds)) {
                    continue;
                }

                $yearDiff = $highestYears - $years;
                $awardedAt = $highestDate->copy()->subYears($yearDiff);

                DB::table('award_member')->insert([
                    'member_id' => $memberId,
                    'award_id' => $awardId,
                    'approved' => true,
                    'reason' => 'Auto-granted: earned higher tier award',
                    'created_at' => $awardedAt,
                    'updated_at' => $awardedAt,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('award_member')
            ->where('reason', 'Auto-granted: earned higher tier award')
            ->delete();
    }
};
