<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tenureAwardIds = [18, 19, 139, 140];

    public function up(): void
    {
        $csvPath = base_path('tenure-data.csv');

        if (! file_exists($csvPath)) {
            echo "Warning: tenure-data.csv not found at {$csvPath}. Skipping import.\n";

            return;
        }

        $handle = fopen($csvPath, 'r');
        if (! $handle) {
            throw new RuntimeException("Could not open tenure-data.csv");
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $awardId = $row[1] ?? null;
            $clanId = $row[2] ?? null;
            $justification = $row[3] ?? null;
            $timeAwarded = $row[4] ?? null;

            if (! $awardId || ! $clanId || ! $timeAwarded) {
                $skipped++;
                continue;
            }

            if (! in_array((int) $awardId, $this->tenureAwardIds)) {
                $skipped++;
                continue;
            }

            $memberExists = DB::table('members')->where('clan_id', $clanId)->exists();
            if (! $memberExists) {
                $skipped++;
                continue;
            }

            $awardedAt = Carbon::createFromTimestamp((int) $timeAwarded);

            $existing = DB::table('award_member')
                ->where('award_id', $awardId)
                ->where('member_id', $clanId)
                ->first();

            if ($existing) {
                DB::table('award_member')
                    ->where('id', $existing->id)
                    ->update([
                        'reason' => $justification,
                        'created_at' => $awardedAt,
                        'updated_at' => $awardedAt,
                    ]);
                $updated++;
            } else {
                DB::table('award_member')->insert([
                    'award_id' => $awardId,
                    'member_id' => $clanId,
                    'requester_id' => null,
                    'reason' => $justification,
                    'approved' => true,
                    'created_at' => $awardedAt,
                    'updated_at' => $awardedAt,
                ]);
                $imported++;
            }
        }

        fclose($handle);

        echo "Tenure data import complete: {$imported} inserted, {$updated} updated, {$skipped} skipped.\n";
    }

    public function down(): void
    {
        echo "Note: down() does not remove imported tenure awards to preserve data integrity.\n";
    }
};
