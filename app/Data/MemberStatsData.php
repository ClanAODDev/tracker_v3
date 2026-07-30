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
        $divisionComparison = $division
            ? $memberRepository->getDivisionComparison($member, $division)
            : null;

        return new self(
            tenure: TenureData::fromMember($member),
            activity: ActivityData::fromMember($member, $division),
            recruiting: RecruitingData::fromRecruits($member->recruits),
            awards: AwardsData::fromAwards($member->awards),
            divisionComparison: $divisionComparison,
        );
    }
}
