<?php

namespace Tests\Unit\Authorization;

use App\Authorization\UnitHierarchy;
use App\Authorization\UnitTreeHierarchy;
use App\Enums\Position;
use App\Enums\UnitLevel;
use App\Models\DivisionUnitLevel;
use App\Models\Member;
use App\Models\User;
use App\Services\Units\UnitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class PlatoonSquadHierarchyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private UnitHierarchy $units;

    protected function setUp(): void
    {
        parent::setUp();

        $this->units = $this->app->make(UnitHierarchy::class);
    }

    #[Test]
    public function units_back_the_bound_hierarchy(): void
    {
        $this->assertInstanceOf(UnitTreeHierarchy::class, $this->units);
    }

    #[Test]
    public function leadership_tier_comes_from_the_unit_a_member_leads(): void
    {
        $squad    = $this->createSquad();
        $platoon  = $squad->platoon;
        $pl       = $this->createPlatoonLeader($platoon);
        $sl       = $this->createSquadLeader($squad);
        $withUser = fn ($member) => User::factory()->create(['member_id' => $member->id]);

        $this->assertSame(UnitLevel::Platoon, $this->units->leadershipLevel($withUser($pl)));
        $this->assertSame(UnitLevel::Squad, $this->units->leadershipLevel($withUser($sl)));

        $titleOnly = $this->createMemberWithUser(['division_id' => $platoon->division_id, 'position' => Position::PLATOON_LEADER]);
        $this->assertNull($this->units->leadershipLevel($titleOnly));
        $this->assertNull($this->units->leadershipLevel($this->createMemberWithUser()->setRelation('member', null)));
    }

    #[Test]
    public function in_a_one_level_division_the_only_level_has_platoon_powers(): void
    {
        $platoon = $this->createPlatoon();
        DivisionUnitLevel::where('division_id', $platoon->division_id)->where('depth', 2)->delete();
        DivisionUnitLevel::updateOrCreate(['division_id' => $platoon->division_id, 'depth' => 1], ['label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Lead']);
        $leader = $this->createPlatoonLeader($platoon);

        $this->assertSame(UnitLevel::Platoon, $this->units->leadershipLevel(User::factory()->create(['member_id' => $leader->id])));
    }

    #[Test]
    public function leads_is_true_only_for_the_units_leader(): void
    {
        $squad   = $this->createSquad();
        $platoon = $squad->platoon;
        $leader  = $this->createSquadLeader($squad);
        $other   = $this->createMember(['division_id' => $platoon->division_id, 'platoon_id' => $platoon->id, 'squad_id' => $squad->id]);
        $pl      = $this->createPlatoonLeader($platoon);

        $this->assertTrue($this->units->leads($leader, $squad));
        $this->assertFalse($this->units->leads($other, $squad));
        $this->assertTrue($this->units->leads($pl, $platoon->fresh()));
        $this->assertFalse($this->units->leads($leader, $platoon->fresh()));

        $assignment = app(UnitAssignment::class);
        $assignment->setLeader($assignment->forLegacy($squad), null);
        $this->assertFalse($this->units->leads($leader, $squad->fresh()));
    }

    #[Test]
    public function leads_unit_of_covers_the_members_squad_and_platoon_leaders(): void
    {
        $squad     = $this->createSquad();
        $platoon   = $squad->platoon;
        $sl        = $this->createSquadLeader($squad);
        $pl        = $this->createPlatoonLeader($platoon);
        $member    = $this->createMember(['division_id' => $platoon->division_id, 'platoon_id' => $platoon->id, 'squad_id' => $squad->id]);
        $elsewhere = $this->createMember(['division_id' => $platoon->division_id]);

        $this->assertTrue($this->units->leadsUnitOf($sl, $member));
        $this->assertTrue($this->units->leadsUnitOf($pl, $member));
        $this->assertFalse($this->units->leadsUnitOf($sl, $elsewhere));
        $this->assertFalse($this->units->leadsUnitOf($pl, $elsewhere));
    }

    #[Test]
    public function shares_led_unit_and_scope_compare_the_leaders_own_assignment(): void
    {
        $squad    = $this->createSquad();
        $division = $squad->platoon->division_id;
        $leader   = $this->createMember(['division_id' => $division, 'platoon_id' => $squad->platoon_id, 'squad_id' => $squad->id]);
        $sameSq   = $this->createMember(['division_id' => $division, 'platoon_id' => $squad->platoon_id, 'squad_id' => $squad->id]);
        $otherSq  = $this->createMember(['division_id' => $division, 'platoon_id' => $squad->platoon_id, 'squad_id' => $this->createSquad($squad->platoon)->id]);

        $this->assertTrue($this->units->sharesLedUnit($leader, $sameSq, UnitLevel::Squad));
        $this->assertFalse($this->units->sharesLedUnit($leader, $otherSq, UnitLevel::Squad));
        $this->assertTrue($this->units->sharesLedUnit($leader, $otherSq, UnitLevel::Platoon));

        $inSquad   = $this->units->scopeToLedUnit(Member::query(), $leader, UnitLevel::Squad)->pluck('id');
        $inPlatoon = $this->units->scopeToLedUnit(Member::query(), $leader, UnitLevel::Platoon)->pluck('id');

        $this->assertEqualsCanonicalizing([$leader->id, $sameSq->id], $inSquad->all());
        $this->assertEqualsCanonicalizing([$leader->id, $sameSq->id, $otherSq->id], $inPlatoon->all());
    }
}
