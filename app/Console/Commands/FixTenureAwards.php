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

            [$eligibleAwardId, $milestone] =
                $this->determineEligibleAward($this->tenureAwardIds, $yearsOfService);

            $missingAwards += $this->processMissingAwards(
                $member,
                $eligibleAwardId,
                $milestone,
                $persistChanges
            );
            $invalidAwards += $this->processInvalidAwards(
                $member,
                $this->tenureAwardIds,
                $yearsOfService,
                $persistChanges
            );
            $extraneousAwards += $this->processExtraneousAwards(
                $member,
                $this->tenureAwardIds,
                $milestone,
                $persistChanges
            );
        }

        $this->info('Tenure awards summary:');
        $this->info(sprintf(
            '- Missing awards %s: %d',
            $persistChanges ? 'added' : 'found',
            $missingAwards
        ));
        $this->info(sprintf(
            '- Invalid awards %s: %d',
            $persistChanges ? 'removed' : 'found',
            $invalidAwards
        ));
        $this->info(sprintf(
            '- Extraneous awards %s: %d',
            $persistChanges ? 'removed' : 'found',
            $extraneousAwards
        ));

        if (! $persistChanges) {
            $this->newLine()->warn(
                'No changes made. Run with --persist flag to perform corrections'
            );
        }
    }

    /**
     * Determine the highest milestone award they're eligible for today.
     */
    private function determineEligibleAward(array $tenureAwards, int $yearsOfService): array
    {
        krsort($tenureAwards);
        foreach ($tenureAwards as $years => $awardId) {
            if ($yearsOfService >= $years) {
                return [$awardId, $years];
            }
        }

        return [null, null];
    }

    /**
     * Add any missing tenure award for the current milestone,
     * and also the _upcoming_ milestone if this is their join‑month.
     */
    private function processMissingAwards($member, $eligibleAwardId, $milestone, $persistChanges): int
    {
        $count = 0;
        $joinDate = Carbon::parse($member->join_date);
        $now = Carbon::now();
        $years = $joinDate->diffInYears($now);
        $currentMon = $now->month;

        $candidates = [];
        if ($eligibleAwardId) {
            $candidates[] = [$eligibleAwardId, $milestone];
        }

        if ($joinDate->month === $currentMon) {
            [$nextAwardId, $nextMilestone] =
                $this->determineEligibleAward($this->tenureAwardIds, $years + 1);

            if ($nextAwardId && $nextMilestone > $milestone) {
                $candidates[] = [$nextAwardId, $nextMilestone];
            }
        }

        foreach ($candidates as [$awardId, $awardYears]) {
            $has = MemberAward::where('member_id', $member->clan_id)
                ->where('award_id', $awardId)
                ->exists();

            if (! $has) {
                if ($persistChanges) {
                    MemberAward::firstOrCreate(
                        [
                            'member_id' => $member->clan_id,
                            'award_id' => $awardId,
                        ],
                        [
                            'reason' => "Awarded for reaching the {$awardYears}-year milestone.",
                            'approved' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
                $count++;
            }
        }

        return $count;
    }

    /**
     * Remove any tenure awards they’re no longer old enough for.
     */
    private function processInvalidAwards($member, array $tenureAwards, int $yearsOfService, $persistChanges): int
    {
        $invalidCount = 0;
        $joinDate = Carbon::parse($member->join_date);
        $now = Carbon::now();
        $currentMonth = $now->month;

        [, $milestone] = $this->determineEligibleAward($tenureAwards, $yearsOfService);

        $upcomingAwardId = null;
        if ($joinDate->month === $currentMonth) {
            [$nextAwardId, $nextMilestone] = $this->determineEligibleAward($tenureAwards, $yearsOfService + 1);
            if ($nextAwardId && $nextMilestone > $milestone) {
                $upcomingAwardId = $nextAwardId;
            }
        }

        $awards = MemberAward::where('member_id', $member->clan_id)
            ->whereIn('award_id', array_values($tenureAwards))
            ->get();

        foreach ($awards as $award) {
            if ($award->award_id === $upcomingAwardId) {
                continue;
            }

            $isValid = false;
            foreach ($tenureAwards as $years => $awardId) {
                if ($award->award_id === $awardId && $yearsOfService >= $years) {
                    $isValid = true;
                    break;
                }
            }

            if (! $isValid) {
                if ($persistChanges) {
                    $award->delete();
                }
                $invalidCount++;
            }
        }

        return $invalidCount;
    }

    /**
     * Remove all lower‑level tenure awards once a higher milestone is earned.
     */
    private function processExtraneousAwards($member, array $tenureAwards, $milestone, $persistChanges): int
    {
        if (! $milestone) {
            return 0;
        }

        $joinDate = Carbon::parse($member->join_date);
        $now = Carbon::now();
        $years = $joinDate->diffInYears($now);
        $month = $now->month;

        $effectiveMilestone = $milestone;

        if ($joinDate->month === $month) {
            [, $nextMilestone] = $this->determineEligibleAward($tenureAwards, $years + 1);
            if ($nextMilestone > $effectiveMilestone) {
                $effectiveMilestone = $nextMilestone;
            }
        }

        $toRemoveIds = collect($tenureAwards)
            ->filter(fn ($id, $years) => $years < $effectiveMilestone)
            ->values()
            ->toArray();

        $toRemove = MemberAward::where('member_id', $member->clan_id)
            ->whereIn('award_id', $toRemoveIds)
            ->get();

        $removed = 0;
        foreach ($toRemove as $award) {
            if ($persistChanges) {
                $award->delete();
            }
            $removed++;
        }

        return $removed;
    }
}
