<?php

namespace Tests\Unit\Policies;

use App\Enums\Rank;
use App\Enums\Role;
use App\Models\DivisionTag;
use App\Policies\DivisionTagPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function admin_bypasses_all_checks()
    {
        $admin = $this->createAdmin();

        $this->assertTrue($this->policy->before($admin));
    }

    #[Test]
    public function regular_user_does_not_bypass_checks()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertNull($this->policy->before($user));
    }

    #[Test]
    public function view_any_returns_true()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertTrue($this->policy->viewAny($user));
    }

    #[Test]
    public function view_returns_true()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $tag      = DivisionTag::factory()->create(['division_id' => $division->id]);

        $this->assertTrue($this->policy->view($user, $tag));
    }

    #[Test]
    public function sr_ldr_can_create_tags()
    {
        $srLdr = $this->createSeniorLeader();

        $this->assertTrue($this->policy->create($srLdr));
    }

    #[Test]
    public function officer_cannot_create_tags()
    {
        $officer = $this->createOfficer();

        $this->assertFalse($this->policy->create($officer));
    }

    #[Test]
    public function member_cannot_create_tags()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();

        $this->assertFalse($this->policy->create($user));
    }

    #[Test]
    public function sr_ldr_can_update_own_division_tag()
    {
        $srLdr = $this->createSeniorLeader();
        $tag   = DivisionTag::factory()->create([
            'division_id' => $srLdr->member->division_id,
        ]);

        $this->assertTrue($this->policy->update($srLdr, $tag));
    }

    #[Test]
    public function sr_ldr_cannot_update_other_division_tag()
    {
        $srLdr         = $this->createSeniorLeader();
        $otherDivision = $this->createActiveDivision();
        $tag           = DivisionTag::factory()->create([
            'division_id' => $otherDivision->id,
        ]);

        $this->assertFalse($this->policy->update($srLdr, $tag));
    }

    #[Test]
    public function sr_ldr_cannot_update_global_tag()
    {
        $srLdr = $this->createSeniorLeader();
        $tag   = DivisionTag::factory()->global()->create();

        $this->assertFalse($this->policy->update($srLdr, $tag));
    }

    #[Test]
    public function sr_ldr_can_delete_own_division_tag()
    {
        $srLdr = $this->createSeniorLeader();
        $tag   = DivisionTag::factory()->create([
            'division_id' => $srLdr->member->division_id,
        ]);

        $this->assertTrue($this->policy->delete($srLdr, $tag));
    }

    #[Test]
    public function sr_ldr_cannot_delete_other_division_tag()
    {
        $srLdr         = $this->createSeniorLeader();
        $otherDivision = $this->createActiveDivision();
        $tag           = DivisionTag::factory()->create([
            'division_id' => $otherDivision->id,
        ]);

        $this->assertFalse($this->policy->delete($srLdr, $tag));
    }

    #[Test]
    public function sr_ldr_cannot_delete_global_tag()
    {
        $srLdr = $this->createSeniorLeader();
        $tag   = DivisionTag::factory()->global()->create();

        $this->assertFalse($this->policy->delete($srLdr, $tag));
    }

    #[Test]
    public function officer_can_assign_tags_to_own_division_member()
    {
        $officer = $this->createOfficer();
        $member  = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->assertTrue($this->policy->assign($officer, $member));
    }

    #[Test]
    public function officer_cannot_assign_tags_to_member_in_different_division()
    {
        $officer       = $this->createOfficer(memberAttributes: ['rank' => Rank::CORPORAL]);
        $otherDivision = $this->createActiveDivision();
        $member        = $this->createMember(['division_id' => $otherDivision->id]);

        $this->assertFalse($this->policy->assign($officer, $member));
    }

    #[Test]
    public function sergeant_can_assign_tags_to_member_in_any_division()
    {
        $division      = $this->createActiveDivision();
        $user          = $this->createMemberWithUser(['division_id' => $division->id, 'rank' => Rank::SERGEANT]);
        $otherDivision = $this->createActiveDivision();
        $member        = $this->createMember(['division_id' => $otherDivision->id]);

        $this->assertTrue($this->policy->assign($user, $member));
    }

    #[Test]
    public function sergeant_cannot_assign_tags_to_ex_aod_member()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id, 'rank' => Rank::SERGEANT]);
        $member   = $this->createMember(['division_id' => 0]);

        $this->assertFalse($this->policy->assign($user, $member));
    }

    #[Test]
    public function master_sergeant_can_assign_tags_to_ex_aod_member()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id, 'rank' => Rank::MASTER_SERGEANT]);
        $member   = $this->createMember(['division_id' => 0]);

        $this->assertTrue($this->policy->assign($user, $member));
    }

    #[Test]
    public function rank_below_sergeant_without_officer_role_cannot_assign_tags()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id, 'rank' => Rank::CORPORAL]);

        $this->assertFalse($this->policy->assign($user, null));
    }

    #[Test]
    public function senior_leader_can_assign_to_member_in_any_division()
    {
        $srLdr         = $this->createSeniorLeader();
        $otherDivision = $this->createActiveDivision();
        $member        = $this->createMember(['division_id' => $otherDivision->id]);

        $this->assertTrue($this->policy->assign($srLdr, $member));
    }
}
