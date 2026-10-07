<?php

namespace Tests\Feature\Authorization;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\UnitLeaderPower;
use App\Enums\UnitLevel;
use App\Models\Member;
use App\Models\RankAction;
use App\Models\User;
use App\Policies\RankActionPolicy;
use App\Services\Units\UnitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitLeaderPowersTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private User $platoonLeader;

    private User $squadLeader;

    private Member $inSquad;

    private Member $inOtherSquad;

    private Member $inOtherPlatoon;

    private $platoon;

    private $squad;

    protected function setUp(): void
    {
        parent::setUp();

        $division      = $this->createActiveDivision();
        $this->platoon = $this->createPlatoon($division);
        $this->squad   = $this->createSquad($this->platoon);
        $otherSquad    = $this->createSquad($this->platoon);
        $otherPlatoon  = $this->createPlatoon($division);
        $member        = fn ($platoon, $squad = null) => $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id, 'squad_id' => $squad?->id ?? 0, 'rank' => Rank::PRIVATE]);

        $this->platoonLeader = $this->createMemberWithUser(['division_id' => $division->id, 'platoon_id' => $this->platoon->id, 'position' => Position::PLATOON_LEADER, 'rank' => Rank::STAFF_SERGEANT]);
        $this->squadLeader   = $this->createMemberWithUser(['division_id' => $division->id, 'platoon_id' => $this->platoon->id, 'squad_id' => $this->squad->id, 'position' => Position::SQUAD_LEADER, 'rank' => Rank::CORPORAL]);
        $this->makeLeader($this->platoonLeader, $this->platoon);
        $this->makeLeader($this->squadLeader, $this->squad);

        $this->inSquad        = $member($this->platoon, $this->squad);
        $this->inOtherSquad   = $member($this->platoon, $otherSquad);
        $this->inOtherPlatoon = $member($otherPlatoon);
    }

    #[Test]
    public function every_power_has_a_description_for_each_tier_it_applies_to(): void
    {
        $division = $this->platoon->division;
        $levels   = $division->unitLevels->map->only('depth', 'label', 'label_plural');
        $limit    = Rank::from($division->settings()->get('max_platoon_leader_rank'));

        foreach (UnitLeaderPower::cases() as $power) {
            foreach (UnitLevel::cases() as $tier) {
                $text = $power->describe($tier, $levels, $tier->value, $limit);
                $this->assertSame($power->appliesTo($tier), $text !== null, "{$power->name} at {$tier->name}");
            }
        }

        $this->assertSame('Request promotions for members of their squad ranked below Specialist', UnitLeaderPower::RequestPromotions->describe(UnitLevel::Squad, $levels, 2, $limit));
        $this->assertSame('Approve promotions in their platoon and every squad under it up to Private First Class', UnitLeaderPower::ApprovePromotions->describe(UnitLevel::Platoon, $levels, 1, $limit));
        $this->assertCount(6, UnitLeaderPower::forDivision($division, 1));
        $this->assertCount(2, UnitLeaderPower::forDivision($division, 2));
    }

    #[Test]
    public function see_rank_actions_covers_the_platoon_only_for_platoon_leaders(): void
    {
        $this->assertTrue(RankActionPolicy::update($this->platoonLeader, $this->action($this->inOtherSquad, Rank::SPECIALIST)));
        $this->assertFalse(RankActionPolicy::update($this->platoonLeader, $this->action($this->inOtherPlatoon, Rank::SPECIALIST)));
        $this->assertFalse(RankActionPolicy::update($this->squadLeader, $this->action($this->inSquad, Rank::PRIVATE_FIRST_CLASS)));
    }

    #[Test]
    public function request_promotions_covers_the_unit_and_its_rank_limit(): void
    {
        $platoonEligible = Member::eligibleForRankAction($this->platoonLeader)->pluck('id');
        $squadEligible   = Member::eligibleForRankAction($this->squadLeader)->pluck('id');

        $this->assertTrue($platoonEligible->contains($this->inOtherSquad->id));
        $this->assertFalse($platoonEligible->contains($this->inOtherPlatoon->id));
        $this->assertTrue($squadEligible->contains($this->inSquad->id));
        $this->assertFalse($squadEligible->contains($this->inOtherSquad->id));
    }

    #[Test]
    public function approve_and_comment_cover_the_platoon_up_to_the_division_limit(): void
    {
        $this->assertTrue(RankActionPolicy::approve($this->platoonLeader, $this->action($this->inOtherSquad, Rank::PRIVATE_FIRST_CLASS)));
        $this->assertFalse(RankActionPolicy::approve($this->platoonLeader, $this->action($this->inOtherSquad, Rank::SPECIALIST)));
        $this->assertFalse(RankActionPolicy::approve($this->platoonLeader, $this->action($this->inOtherPlatoon, Rank::PRIVATE_FIRST_CLASS)));
        $this->assertFalse(RankActionPolicy::approve($this->squadLeader, $this->action($this->inSquad, Rank::PRIVATE_FIRST_CLASS)));

        $this->assertTrue(RankActionPolicy::manageComments($this->platoonLeader, $this->action($this->inOtherSquad, Rank::PRIVATE_FIRST_CLASS)));
        $this->assertFalse(RankActionPolicy::manageComments($this->platoonLeader, $this->action($this->inOtherPlatoon, Rank::PRIVATE_FIRST_CLASS)));
    }

    #[Test]
    public function manage_unit_covers_the_platoon_and_its_squads_for_platoon_leaders(): void
    {
        $units = app(UnitAssignment::class);

        $this->assertTrue(Gate::forUser($this->platoonLeader)->allows('update', $units->forLegacy($this->platoon)));
        $this->assertTrue(Gate::forUser($this->platoonLeader)->allows('update', $units->forLegacy($this->squad)));
        $this->assertTrue(Gate::forUser($this->platoonLeader)->allows('delete', $units->forLegacy($this->squad)));
        $this->assertFalse(Gate::forUser($this->squadLeader)->allows('update', $units->forLegacy($this->squad)));
    }

    #[Test]
    public function manage_handles_and_fields_covers_the_led_unit(): void
    {
        $this->assertTrue(Gate::forUser($this->platoonLeader)->allows('manageHandles', $this->inOtherSquad));
        $this->assertFalse(Gate::forUser($this->platoonLeader)->allows('manageHandles', $this->inOtherPlatoon));
        $this->assertTrue(Gate::forUser($this->squadLeader)->allows('manageHandles', $this->inSquad));
        $this->assertFalse(Gate::forUser($this->squadLeader)->allows('manageHandles', $this->inOtherSquad));
    }

    private function action(Member $member, Rank $rank): RankAction
    {
        return RankAction::factory()->create(['member_id' => $member->id, 'rank' => $rank]);
    }
}
