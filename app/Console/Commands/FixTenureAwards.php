<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\MemberAward;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixTenureAwards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-tenure-awards {--persist : Persist changes to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Looks for and adds missing tenure awards, removes erroneous awards, as well as removes any extraneous tenure awards';

    /**
     * AOD Service Awards
     */
    private array $tenureAwardIds = [
        20 => 140,
        15 => 139,
        10 => 19,
        5 => 18,
    ];

    public function handle()
    {
        $members = Member::whereHas('division')->get();

        $persistChanges = $this->option('persist');

        $missingAwards = 0;
        $invalidAwards = 0;
        $extraneousAwards = 0;

        foreach ($members as $member) {
            $joinDate = Carbon::parse($member->join_date);
            $yearsOfService = $joinDate->diffInYears(Carbon::now());

            [$eligibleAwardId, $milestone] = $this->determineEligibleAward($this->tenureAwardIds, $yearsOfService);

            $missingAwards += $this->processMissingAwards($member, $eligibleAwardId, $milestone, $persistChanges);
            $invalidAwards += $this->processInvalidAwards($member, $this->tenureAwardIds, $yearsOfService, $persistChanges);
            $extraneousAwards += $this->processExtraneousAwards($member, $this->tenureAwardIds, $milestone, $persistChanges);
        }

        $this->info('Tenure awards assignment complete.');
        $this->info('Summary:');
        $this->info("- Missing awards added: {$missingAwards}");
        $this->info("- Invalid awards removed: {$invalidAwards}");
        $this->info("- Extraneous awards removed: {$extraneousAwards}");
    }

    private function determineEligibleAward(array $tenureAwards, int $yearsOfService): array
    {
        foreach ($tenureAwards as $years => $awardId) {
            if ($yearsOfService >= $years) {
                return [$awardId, $years];
            }
        }

        return [null, null];
    }

    private function processMissingAwards($member, $eligibleAwardId, $milestone, $persistChanges): int
    {
        if (! $eligibleAwardId) {
            return 0;
        }

        $hasAward = MemberAward::where('member_id', $member->clan_id)
            ->where('award_id', $eligibleAwardId)
            ->exists();

        if (! $hasAward) {
            if ($persistChanges) {
                MemberAward::create([
                    'member_id' => $member->clan_id,
                    'award_id' => $eligibleAwardId,
                    'reason' => "Awarded for reaching the $milestone-year milestone.",
                    'approved' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            return 1;
        }

        return 0;
    }

    private function processInvalidAwards($member, array $tenureAwards, int $yearsOfService, $persistChanges): int
    {
        $invalidCount = 0;
        $awards = MemberAward::where('member_id', $member->clan_id)
            ->whereIn('award_id', array_values($tenureAwards))
            ->get();

        foreach ($awards as $award) {
            $isInvalid = true;
            foreach ($tenureAwards as $years => $awardId) {
                if ($yearsOfService >= $years && $award->award_id == $awardId) {
                    $isInvalid = false;
                    break;
                }
            }

            if ($isInvalid) {
                if ($persistChanges) {
                    $award->delete();
                }
                $invalidCount++;
            }
        }

        return $invalidCount;
    }

    private function processExtraneousAwards($member, array $tenureAwards, $milestone, $persistChanges): int
    {
        if (! $milestone) {
            return 0;
        }

        $extraneousAwardIds = collect($tenureAwards)
            ->filter(fn ($id, $years) => $years < $milestone)
            ->values()
            ->toArray();

        $awardsToRemove = MemberAward::where('member_id', $member->clan_id)
            ->whereIn('award_id', $extraneousAwardIds)
            ->get();

        $removedCount = 0;
        foreach ($awardsToRemove as $award) {
            if ($persistChanges) {
                $award->delete();
            }
            $removedCount++;
        }

        return $removedCount;
    }
}
