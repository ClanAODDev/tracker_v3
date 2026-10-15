<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberAward;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TenureAwardService
{
    public const AWARD_IDS = [
        30 => 344,
        25 => 343,
        20 => 140,
        15 => 139,
        10 => 19,
        5  => 18,
    ];

    public function reconcile(Builder $members, bool $persist, bool $removeInvalid = true, ?Carbon $today = null): array
    {
        $today ??= now();
        $stats = ['missing' => 0, 'invalid' => 0, 'granted' => collect()];

        $members
            ->whereNotNull('join_date')
            ->chunkById(500, function (Collection $chunk) use ($persist, $removeInvalid, $today, &$stats) {
                $held = MemberAward::whereIn('member_id', $chunk->pluck('id'))
                    ->whereIn('award_id', array_values(self::AWARD_IDS))
                    ->get()
                    ->groupBy('member_id');

                foreach ($chunk as $member) {
                    $eligible = $this->eligibleAwardIds($member, $today);
                    $existing = $held->get($member->id, collect());

                    $owed = $this->awardIdsOwed($eligible, $existing);

                    $granted = $this->grantMissing($member, $owed, $existing, $persist);

                    $stats['missing'] += $granted['count'];
                    $stats['granted'] = $stats['granted']->concat($granted['created']);
                    $stats['invalid'] += $this->removeInvalid($owed, $existing, $persist && $removeInvalid);
                }
            });

        return $stats;
    }

    public function eligibleAwardIds(Member $member, Carbon $today): array
    {
        $joined = $member->join_date->copy()->startOfDay();
        $today  = $today->copy()->startOfDay();
        $years  = (int) floor($joined->diffInYears($today));

        $anniversaryClampedToMonthEnd = $today->isSameDay($today->copy()->endOfMonth())
            && $joined->month === $today->month
            && $joined->day > $today->day;

        if ($anniversaryClampedToMonthEnd) {
            $years++;
        }

        return array_filter(
            self::AWARD_IDS,
            fn (int $awardId, int $milestone) => $years >= $milestone,
            ARRAY_FILTER_USE_BOTH,
        );
    }

    private function awardIdsOwed(array $eligible, Collection $existing): array
    {
        $heldMilestones = $existing
            ->where('approved', true)
            ->map(fn (MemberAward $award) => array_search($award->award_id, self::AWARD_IDS, strict: true))
            ->all();

        $ceiling = max([0, ...array_keys($eligible), ...$heldMilestones]);

        return array_filter(
            self::AWARD_IDS,
            fn (int $awardId, int $milestone) => $milestone <= $ceiling,
            ARRAY_FILTER_USE_BOTH,
        );
    }

    private function grantMissing(Member $member, array $eligible, Collection $existing, bool $persist): array
    {
        $count   = 0;
        $created = collect();

        foreach ($eligible as $milestone => $awardId) {
            if ($existing->contains('award_id', $awardId)) {
                continue;
            }

            if ($persist) {
                $memberAward = MemberAward::firstOrCreate(
                    ['member_id' => $member->id, 'award_id' => $awardId],
                    ['reason' => "Awarded for reaching the {$milestone}-year milestone.", 'approved' => true],
                );

                if ($memberAward->wasRecentlyCreated) {
                    $created->push($memberAward);
                }
            }

            $count++;
        }

        return ['count' => $count, 'created' => $created];
    }

    private function removeInvalid(array $eligible, Collection $existing, bool $persist): int
    {
        $invalid = $existing->reject(fn (MemberAward $award) => in_array($award->award_id, $eligible, strict: true));

        if ($persist) {
            $invalid->each->delete();
        }

        return $invalid->count();
    }
}
