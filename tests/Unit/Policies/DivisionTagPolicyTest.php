<?php

namespace Tests\Unit\Policies;

use App\Enums\Position;
use App\Enums\Role;
use App\Models\DivisionTag;
use App\Policies\DivisionTagPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionTagPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private DivisionTagPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new DivisionTagPolicy;
    }

    public function test_admin_bypasses_all_checks()
    {
        $admin = $this->createAdmin();

        $this->assertTrue($this->policy->before($admin));
    }

    public function test_regular_user_does_not_bypass_checks()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertNull($this->policy->before($user));
    }

    public function test_view_any_returns_true()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_returns_true()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $tag      = DivisionTag::factory()->create(['division_id' => $division->id]);

        $this->assertTrue($this->policy->view($user, $tag));
    }

    public function test_sr_ldr_can_create_tags()
    {
        $srLdr = $this->createSeniorLeader();

        $this->assertTrue($this->policy->create($srLdr));
    }

    public function test_officer_cannot_create_tags()
    {
        $officer = $this->createOfficer();

        $this->assertFalse($this->policy->create($officer));
    }

    public function test_member_cannot_create_tags()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();

        $this->assertFalse($this->policy->create($user));
    }

    public function test_sr_ldr_can_update_own_division_tag()
    {
        $srLdr = $this->createSeniorLeader();
        $tag   = DivisionTag::factory()->create([
            'division_id' => $srLdr->member->division_id,
        ]);

        $this->assertTrue($this->policy->update($srLdr, $tag));
    }

    public function test_sr_ldr_cannot_update_other_division_tag()
    {
        $srLdr         = $this->createSeniorLeader();
        $otherDivision = $this->createActiveDivision();
        $tag           = DivisionTag::factory()->create([
            'division_id' => $otherDivision->id,
        ]);

        $this->assertFalse($this->policy->update($srLdr, $tag));
    }

    public function test_sr_ldr_cannot_update_global_tag()
    {
        $srLdr = $this->createSeniorLeader();
        $tag   = DivisionTag::factory()->global()->create();

        $this->assertFalse($this->policy->update($srLdr, $tag));
    }

    public function test_sr_ldr_can_delete_own_division_tag()
    {
        $srLdr = $this->createSeniorLeader();
        $tag   = DivisionTag::factory()->create([
            'division_id' => $srLdr->member->division_id,
        ]);

        $this->assertTrue($this->policy->delete($srLdr, $tag));
    }

    public function test_sr_ldr_cannot_delete_other_division_tag()
    {
        $srLdr         = $this->createSeniorLeader();
        $otherDivision = $this->createActiveDivision();
        $tag           = DivisionTag::factory()->create([
            'division_id' => $otherDivision->id,
        ]);

        $this->assertFalse($this->policy->delete($srLdr, $tag));
    }

    public function test_sr_ldr_cannot_delete_global_tag()
    {
        $srLdr = $this->createSeniorLeader();
        $tag   = DivisionTag::factory()->global()->create();

        $this->assertFalse($this->policy->delete($srLdr, $tag));
    }

    public function test_sr_ldr_can_assign_tags()
    {
        $srLdr = $this->createSeniorLeader();

        $this->assertTrue($this->policy->assign($srLdr, null));
    }

    public function test_platoon_leader_can_assign_tags()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::PLATOON_LEADER,
        ]);

        $this->assertTrue($this->policy->assign($user, null));
    }

    public function test_squad_leader_can_assign_tags()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => $squad->id,
            'position'    => Position::SQUAD_LEADER,
        ]);

        $this->assertTrue($this->policy->assign($user, null));
    }

    public function test_officer_can_assign_tags()
    {
        $officer = $this->createOfficer();

        $this->assertTrue($this->policy->assign($officer, null));
    }

    public function test_member_cannot_assign_tags()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::MEMBER,
        ]);
        $user->role = Role::MEMBER;
        $user->save();

        $this->assertFalse($this->policy->assign($user, null));
    }

    public function test_can_assign_to_member_in_same_division()
    {
        $srLdr  = $this->createSeniorLeader();
        $member = $this->createMember([
            'division_id' => $srLdr->member->division_id,
        ]);

        $this->assertTrue($this->policy->assign($srLdr, $member));
    }

    public function test_cannot_assign_to_member_in_different_division()
    {
        $srLdr         = $this->createSeniorLeader();
        $otherDivision = $this->createActiveDivision();
        $member        = $this->createMember([
            'division_id' => $otherDivision->id,
        ]);

        $this->assertFalse($this->policy->assign($srLdr, $member));
    }
}
