<?php

namespace Tests\Unit\Authorization;

use App\Authorization\UnitTreeHierarchy;
use App\Enums\UnitLevel;
use App\Models\Member;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitTreeHierarchyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private UnitTreeHierarchy $tree;

    private Unit $p1;

    private Unit $p2;

    private Unit $s1a;

    private Unit $s1b;

    private Unit $s2a;

    private Member $platoonLeader;

    private Member $squadLeader;

    private Member $inP1;

    private Member $inS1a;

    private Member $inS1b;

    private Member $inS2a;

    private Member $unassigned;

    protected function setUp(): void
    {
        parent::setUp();

        $division = $this->createActiveDivision();

        $this->p1  = $this->createPlatoon($division);
        $this->p2  = $this->createPlatoon($division);
        $this->s1a = $this->createSquad($this->p1);
        $this->s1b = $this->createSquad($this->p1);
        $this->s2a = $this->createSquad($this->p2);

        $this->platoonLeader = $this->createPlatoonLeader($this->p1);
        $this->squadLeader   = $this->createSquadLeader($this->s1a);
        $this->inP1          = $this->createMember(['division_id' => $division->id, 'unit_id' => $this->p1->id]);
        $this->inS1a         = $this->createMember(['division_id' => $division->id, 'unit_id' => $this->s1a->id]);
        $this->inS1b         = $this->createMember(['division_id' => $division->id, 'unit_id' => $this->s1b->id]);
        $this->inS2a         = $this->createMember(['division_id' => $division->id, 'unit_id' => $this->s2a->id]);
        $this->unassigned    = $this->createMember(['division_id' => $division->id]);

        $this->tree = $this->app->make(UnitTreeHierarchy::class);
    }

    #[Test]
    public function leads_is_true_only_for_the_unit_a_member_is_leader_of(): void
    {
        $this->assertTrue($this->tree->leads($this->platoonLeader, $this->p1->fresh()));
        $this->assertFalse($this->tree->leads($this->platoonLeader, $this->s1a->fresh()));
        $this->assertFalse($this->tree->leads($this->platoonLeader, $this->p2->fresh()));
        $this->assertTrue($this->tree->leads($this->squadLeader, $this->s1a->fresh()));
        $this->assertFalse($this->tree->leads($this->squadLeader, $this->s1b->fresh()));
        $this->assertFalse($this->tree->leads($this->unassigned, $this->p1->fresh()));
    }

    #[Test]
    public function leads_unit_of_walks_up_the_tree(): void
    {
        $this->assertTrue($this->tree->leadsUnitOf($this->platoonLeader, $this->inP1->fresh()));
        $this->assertTrue($this->tree->leadsUnitOf($this->platoonLeader, $this->inS1a->fresh()));
        $this->assertTrue($this->tree->leadsUnitOf($this->platoonLeader, $this->inS1b->fresh()));
        $this->assertFalse($this->tree->leadsUnitOf($this->platoonLeader, $this->inS2a->fresh()));
        $this->assertFalse($this->tree->leadsUnitOf($this->platoonLeader, $this->unassigned->fresh()));

        $this->assertTrue($this->tree->leadsUnitOf($this->squadLeader, $this->inS1a->fresh()));
        $this->assertFalse($this->tree->leadsUnitOf($this->squadLeader, $this->inS1b->fresh()));
        $this->assertFalse($this->tree->leadsUnitOf($this->squadLeader, $this->inP1->fresh()));
    }

    #[Test]
    public function shares_led_unit_compares_the_unit_at_the_given_level(): void
    {
        $this->assertTrue($this->tree->sharesLedUnit($this->platoonLeader, $this->inS1b->fresh(), UnitLevel::Platoon));
        $this->assertFalse($this->tree->sharesLedUnit($this->platoonLeader, $this->inS2a->fresh(), UnitLevel::Platoon));
        $this->assertTrue($this->tree->sharesLedUnit($this->squadLeader, $this->inS1a->fresh(), UnitLevel::Squad));
        $this->assertFalse($this->tree->sharesLedUnit($this->squadLeader, $this->inS1b->fresh(), UnitLevel::Squad));
    }

    #[Test]
    public function scope_to_led_unit_limits_members_to_the_led_subtree(): void
    {
        $platoon = $this->tree->scopeToLedUnit(Member::query(), $this->platoonLeader->fresh(), UnitLevel::Platoon)->pluck('id');
        $squad   = $this->tree->scopeToLedUnit(Member::query(), $this->squadLeader->fresh(), UnitLevel::Squad)->pluck('id');

        $this->assertEqualsCanonicalizing(
            [$this->platoonLeader->id, $this->squadLeader->id, $this->inP1->id, $this->inS1a->id, $this->inS1b->id],
            $platoon->all(),
        );
        $this->assertEqualsCanonicalizing([$this->squadLeader->id, $this->inS1a->id], $squad->all());
    }

    #[Test]
    public function leadership_level_reflects_the_shallowest_unit_led(): void
    {
        $this->assertSame(UnitLevel::Platoon, $this->tree->leadershipLevel($this->userFor($this->platoonLeader)));
        $this->assertSame(UnitLevel::Squad, $this->tree->leadershipLevel($this->userFor($this->squadLeader)));
        $this->assertNull($this->tree->leadershipLevel($this->userFor($this->unassigned)));
    }

    private function userFor(Member $member): User
    {
        return User::factory()->create(['member_id' => $member->id, 'name' => $member->name]);
    }
}
