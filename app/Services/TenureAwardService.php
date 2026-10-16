<?php

namespace App\Services;

use App\Data\TenureReconciliation;
use App\Exceptions\TenureReconciliationInProgress;
use App\Models\Award;
use App\Models\Member;
use App\Models\MemberAward;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class TenureAwardService
{
    public const MILESTONES = [30, 25, 20, 15, 10, 5];

    private ?array $awardIds = null;

    public function awardIds(): array
    {
        if ($this->awardIds !== null) {
            return $this->awardIds;
        }

        $names = collect(self::MILESTONES)->mapWithKeys(fn (int $years) => [$years => "{$years} Years of Service"]);

        $found = Award::whereNull('division_id')
            ->whereIn('name', $names->values())
            ->pluck('id', 'name');

        $missing = $names->reject(fn (string $name) => $found->has($name));

        if ($missing->isNotEmpty()) {
            throw new RuntimeException('Missing clan-wide tenure awards: ' . $missing->values()->join(', '));
        }

        return $this->awardIds = $names->map(fn (string $name) => $found[$name])->all();
    }

    public function milestoneOf(MemberAward $memberAward): ?int
    {
        $milestone = array_search($memberAward->award_id, $this->awardIds(), strict: true);

        return $milestone === false ? null : $milestone;
    }

    public function reconcile(Builder $members, bool $persist, bool $removeInvalid = true, ?Carbon $today = null): TenureReconciliation
    {
        if (! $persist) {
            return $this->run($members, persist: false, removeInvalid: $removeInvalid, today: $today);
        }

        $lock = Cache::lock('tenure-award-reconcile', 900);

        if (! $lock->get()) {
            throw new TenureReconciliationInProgress;
        }

        try {
            return $this->run($members, persist: true, removeInvalid: $removeInvalid, today: $today);
        } finally {
            $lock->release();
        }
    }

    private function run(Builder $members, bool $persist, bool $removeInvalid, ?Carbon $today): TenureReconciliation
    {
        $today ??= now();
        $missing = 0;
        $invalid = 0;
        $granted = collect();

        $members
            ->whereNotNull('join_date')
            ->chunkById(500, function (Collection $chunk) use ($persist, $removeInvalid, $today, &$missing, &$invalid, &$granted) {
                $held = MemberAward::whereIn('member_id', $chunk->pluck('id'))
                    ->whereIn('award_id', array_values($this->awardIds()))
                    ->get()
                    ->groupBy('member_id');

                foreach ($chunk as $member) {
                    $eligible = $this->eligibleAwardIds($member, $today);
                    $existing = $held->get($member->id, collect());

                    $owed = $this->awardIdsOwed($eligible, $existing);

                    $grants = $this->grantMissing($member, $owed, $existing, $persist);

                    $missing += $grants['count'];
                    $granted = $granted->concat($grants['created']);
                    $invalid += $this->removeInvalid($owed, $existing, $persist && $removeInvalid);
                }
            });

        return new TenureReconciliation($missing, $invalid, $granted);
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
            $this->awardIds(),
            fn (int $awardId, int $milestone) => $years >= $milestone,
            ARRAY_FILTER_USE_BOTH,
        );
    }

    private function awardIdsOwed(array $eligible, Collection $existing): array
    {
        $heldMilestones = $existing
            ->where('approved', true)
            ->map(fn (MemberAward $award) => $this->milestoneOf($award))
            ->all();

        $ceiling = max([0, ...array_keys($eligible), ...$heldMilestones]);

        return array_filter(
            $this->awardIds(),
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
