<?php

namespace Tests\Feature\Authorization;

use App\Authorization\UnitHierarchy;
use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\UnitLevel;
use App\Models\DivisionUnitLevel;
use App\Models\Member;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DeepUnitScopeTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private User $middleLeader;

    private User $leafLeader;

    private Unit $company;

    private Unit $middle;

    private Unit $leaf;

    private Unit $siblingMiddle;

    private Unit $otherCompany;

    private Member $inLeaf;

    private Member $inSiblingMiddle;

    private Member $inOtherCompany;

    protected function setUp(): void
    {
        parent::setUp();

        $division = $this->createActiveDivision();
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);

        $this->company       = $this->createPlatoon($division);
        $this->middle        = Unit::factory()->childOf($this->company)->create();
        $this->leaf          = Unit::factory()->childOf($this->middle)->create();
        $this->siblingMiddle = Unit::factory()->childOf($this->company)->create();
        $this->otherCompany  = $this->createPlatoon($division);

        $member = fn (Unit $unit) => $this->createMember(['division_id' => $division->id, 'unit_id' => $unit->id, 'rank' => Rank::PRIVATE]);

        $this->middleLeader = $this->createMemberWithUser(['division_id' => $division->id, 'unit_id' => $this->middle->id, 'position' => Position::PLATOON_LEADER, 'rank' => Rank::STAFF_SERGEANT]);
        $this->leafLeader   = $this->createMemberWithUser(['division_id' => $division->id, 'unit_id' => $this->leaf->id, 'position' => Position::SQUAD_LEADER, 'rank' => Rank::CORPORAL]);
        $this->makeLeader($this->middleLeader, $this->middle);
        $this->makeLeader($this->leafLeader, $this->leaf);

        $this->leaf = $this->leaf->fresh();

        $this->inLeaf          = $member($this->leaf);
        $this->inSiblingMiddle = $member($this->siblingMiddle);
        $this->inOtherCompany  = $member($this->otherCompany);
    }

    #[Test]
    public function leadership_tier_follows_the_division_depth(): void
    {
        $units = app(UnitHierarchy::class);

        $this->assertSame(UnitLevel::Platoon, $units->leadershipLevel($this->middleLeader));
        $this->assertSame(UnitLevel::Squad, $units->leadershipLevel($this->leafLeader));
    }

    #[Test]
    public function a_middle_leader_shares_only_their_own_subtree(): void
    {
        $units  = app(UnitHierarchy::class);
        $leader = $this->middleLeader->member;

        $this->assertTrue($units->sharesLedUnit($leader, $this->inLeaf->fresh(), UnitLevel::Platoon));
        $this->assertFalse($units->sharesLedUnit($leader, $this->inSiblingMiddle->fresh(), UnitLevel::Platoon));
        $this->assertFalse($units->sharesLedUnit($leader, $this->inOtherCompany->fresh(), UnitLevel::Platoon));
    }

    #[Test]
    public function member_scopes_follow_the_led_unit(): void
    {
        $units = app(UnitHierarchy::class);

        $middleScope = $units->scopeToLedUnit(Member::query(), $this->middleLeader->member, UnitLevel::Platoon)->pluck('id');
        $leafScope   = $units->scopeToLedUnit(Member::query(), $this->leafLeader->member, UnitLevel::Squad)->pluck('id');

        $this->assertContains($this->inLeaf->id, $middleScope);
        $this->assertNotContains($this->inSiblingMiddle->id, $middleScope);
        $this->assertNotContains($this->inOtherCompany->id, $middleScope);

        $this->assertContains($this->inLeaf->id, $leafScope);
        $this->assertNotContains($this->inSiblingMiddle->id, $leafScope);
    }

    #[Test]
    public function unit_management_follows_the_led_subtree(): void
    {
        $this->assertTrue($this->middleLeader->can('update', $this->middle));
        $this->assertTrue($this->middleLeader->can('update', $this->leaf));
        $this->assertFalse($this->middleLeader->can('update', $this->siblingMiddle));
        $this->assertFalse($this->middleLeader->can('update', $this->company));

        $this->assertTrue($this->middleLeader->can('delete', $this->leaf));
        $this->assertFalse($this->middleLeader->can('delete', $this->middle));

        $this->assertFalse($this->leafLeader->can('update', $this->leaf));
        $this->assertFalse($this->leafLeader->can('update', $this->middle));
    }

    #[Test]
    public function a_middle_leader_can_place_a_member_directly_in_their_own_unit(): void
    {
        $this->actingAs($this->middleLeader)
            ->postJson('/members/assign-squad', ['member_id' => $this->inLeaf->id, 'unit_id' => $this->middle->id])
            ->assertOk();

        $this->assertSame($this->middle->id, $this->inLeaf->fresh()->unit_id);
    }

    #[Test]
    public function a_middle_leader_cannot_place_a_member_in_a_sibling_unit(): void
    {
        $this->actingAs($this->middleLeader)
            ->postJson('/members/assign-squad', ['member_id' => $this->inLeaf->id, 'unit_id' => $this->siblingMiddle->id])
            ->assertForbidden();
    }

    #[Test]
    public function a_bottom_leader_has_no_manage_page(): void
    {
        $this->actingAs($this->leafLeader)
            ->get(route('unit.manage', [$this->leaf->division->slug, $this->middle]))
            ->assertForbidden();
    }
}
