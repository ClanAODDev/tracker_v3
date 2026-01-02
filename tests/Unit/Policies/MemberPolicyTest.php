<?php

namespace Tests\Unit\Policies;

use App\Enums\Rank;
use App\Enums\Role;
use App\Policies\MemberPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private MemberPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new MemberPolicy;
    }

    public function test_admin_bypasses_all_checks()
    {
        $admin = $this->createAdmin();
        $member = $this->createMember(['division_id' => $admin->member->division_id]);

        $this->assertTrue($this->policy->before($admin));
    }

    public function test_developer_bypasses_all_checks()
    {
        $division = $this->createActiveDivision();
        $developer = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $developer->developer = true;
        $developer->save();

        $this->assertTrue($this->policy->before($developer));
    }

    public function test_regular_user_does_not_bypass_checks()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertNull($this->policy->before($user));
    }

    public function test_officer_can_recruit()
    {
        $officer = $this->createOfficer();

        $this->assertTrue($this->policy->recruit($officer));
    }

    public function test_member_cannot_recruit()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();

        $this->assertFalse($this->policy->recruit($user));
    }

    public function test_create_always_returns_false()
    {
        $admin = $this->createAdmin();
        $user = $this->createMemberWithUser(['division_id' => $admin->member->division_id]);

        $this->assertFalse($this->policy->create($admin));
        $this->assertFalse($this->policy->create($user));
    }

    public function test_sr_ldr_can_update_other_members()
    {
        $srLdr = $this->createSeniorLeader();
        $member = $this->createMember(['division_id' => $srLdr->member->division_id]);

        $this->actingAs($srLdr);
        $this->assertTrue($this->policy->update($srLdr, $member));
    }

    public function test_user_cannot_update_themselves()
    {
        $srLdr = $this->createSeniorLeader();

        $this->actingAs($srLdr);
        $this->assertFalse($this->policy->update($srLdr, $srLdr->member));
    }

    public function test_officer_cannot_update_members()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer);
        $this->assertFalse($this->policy->update($officer, $member));
    }

    public function test_officer_can_flag_inactive()
    {
        $officer = $this->createOfficer();

        $this->assertTrue($this->policy->flagInactive($officer));
    }

    public function test_sr_ldr_can_flag_inactive()
    {
        $srLdr = $this->createSeniorLeader();

        $this->assertTrue($this->policy->flagInactive($srLdr));
    }

    public function test_member_cannot_flag_inactive()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();

        $this->assertFalse($this->policy->flagInactive($user));
    }

    public function test_view_returns_true()
    {
        $this->assertTrue($this->policy->view());
    }

    public function test_view_any_returns_true()
    {
        $this->assertTrue($this->policy->viewAny());
    }

    public function test_delete_always_returns_false()
    {
        $this->assertFalse($this->policy->delete());
    }

    public function test_sr_ldr_can_separate_lower_rank_member()
    {
        $srLdr = $this->createSeniorLeader();
        $member = $this->createMember([
            'division_id' => $srLdr->member->division_id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->assertTrue($this->policy->separate($srLdr, $member));
    }

    public function test_sr_ldr_cannot_separate_higher_rank_member()
    {
        $srLdr = $this->createSeniorLeader();
        $member = $this->createMember([
            'division_id' => $srLdr->member->division_id,
            'rank' => Rank::SERGEANT_MAJOR,
        ]);

        $this->assertFalse($this->policy->separate($srLdr, $member));
    }

    public function test_user_cannot_separate_themselves()
    {
        $srLdr = $this->createSeniorLeader();

        $this->assertFalse($this->policy->separate($srLdr, $srLdr->member));
    }

    public function test_officer_cannot_separate_members()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember([
            'division_id' => $officer->member->division_id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->assertFalse($this->policy->separate($officer, $member));
    }

    public function test_user_can_manage_own_part_time()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->actingAs($user);
        $this->assertTrue($this->policy->managePartTime($user, $user->member));
    }

    public function test_officer_can_manage_others_part_time()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer);
        $this->assertTrue($this->policy->managePartTime($officer, $member));
    }

    public function test_member_cannot_manage_others_part_time()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();

        $member = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($user);
        $this->assertFalse($this->policy->managePartTime($user, $member));
    }

    public function test_officer_can_promote_within_division()
    {
        $officer = $this->createOfficer();
        $officer->member->rank = Rank::STAFF_SERGEANT;
        $officer->member->save();

        $member = $this->createMember([
            'division_id' => $officer->member->division_id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->assertTrue($this->policy->promote($officer, $member));
    }

    public function test_officer_cannot_promote_higher_than_one_below_their_rank()
    {
        $officer = $this->createOfficer();
        $officer->member->rank = Rank::STAFF_SERGEANT;
        $officer->member->save();

        $member = $this->createMember([
            'division_id' => $officer->member->division_id,
            'rank' => Rank::STAFF_SERGEANT,
        ]);

        $this->assertFalse($this->policy->promote($officer, $member));
    }

    public function test_officer_cannot_promote_in_different_division()
    {
        $officer = $this->createOfficer();
        $officer->member->rank = Rank::MASTER_SERGEANT;
        $officer->member->save();

        $otherDivision = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $otherDivision->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->assertFalse($this->policy->promote($officer, $member));
    }

    public function test_member_cannot_promote()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();

        $member = $this->createMember([
            'division_id' => $division->id,
            'rank' => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->assertFalse($this->policy->promote($user, $member));
    }

    public function test_user_can_manage_own_handles()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->actingAs($user);
        $this->assertTrue($this->policy->manageIngameHandles($user, $user->member));
    }

    public function test_officer_can_manage_others_handles()
    {
        $officer = $this->createOfficer();
        $member = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer);
        $this->assertTrue($this->policy->manageIngameHandles($officer, $member));
    }
}
