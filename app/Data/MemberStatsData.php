<?php

namespace App\Data;

use App\Models\Division;
use App\Models\Member;
use App\Repositories\MemberRepository;

readonly class MemberStatsData
{
    public function __construct(
        public TenureData $tenure,
        public ActivityData $activity,
        public RecruitingData $recruiting,
        public AwardsData $awards,
        public ?DivisionComparisonData $divisionComparison,
    ) {}

    public static function fromMember(
        Member $member,
        ?Division $division,
        MemberRepository $memberRepository
    ): self {
        $divisionComparison = null;
        if ($division) {
            $comparison = $memberRepository->getDivisionComparison($member, $division);
            if ($comparison) {
                $divisionComparison = new DivisionComparisonData(
                    avgTenureDays: $comparison->avgTenureDays,
                    avgTenureYears: $comparison->avgTenureYears,
                    avgVoiceDays: $comparison->avgVoiceDays,
                    tenurePercentile: $comparison->tenurePercentile,
                    activityPercentile: $comparison->activityPercentile,
                    tenureBetter: $comparison->tenureBetter,
                    activityBetter: $comparison->activityBetter,
                );
            }
        }

        return new self(
            tenure: TenureData::fromMember($member),
            activity: ActivityData::fromMember($member, $division),
            recruiting: RecruitingData::fromRecruits($member->recruits),
            awards: AwardsData::fromAwards($member->awards),
            divisionComparison: $divisionComparison,
        );
    }
}
