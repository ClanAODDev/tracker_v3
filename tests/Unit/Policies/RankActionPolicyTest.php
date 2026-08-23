<?php

namespace Tests\Unit\Policies;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Models\RankAction;
use App\Policies\RankActionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RankActionPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function officer_can_view_any_rank_action_list()
    {
        $officer = $this->createOfficer();

        $this->assertTrue(RankActionPolicy::viewAny($officer));
    }

    #[Test]
    public function member_cannot_view_any_rank_action_list()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertFalse(RankActionPolicy::viewAny($user));
    }

    #[Test]
    public function admin_can_view_any_rank_action()
    {
        $admin    = $this->createAdmin();
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->assertTrue(RankActionPolicy::update($admin, $action));
    }

    #[Test]
    public function user_cannot_view_own_rank_action()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::ADMIN;
        $user->save();

        $action = RankAction::factory()->create([
            'member_id' => $user->member_id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->assertFalse(RankActionPolicy::update($user, $action));
    }

    #[Test]
    public function requester_can_view_their_request()
    {
        $division  = $this->createActiveDivision();
        $requester = $this->createMemberWithUser(['division_id' => $division->id]);
        $member    = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id'    => $member->id,
            'requester_id' => $requester->member_id,
            'rank'         => Rank::CORPORAL,
        ]);

        $this->assertTrue(RankActionPolicy::update($requester, $action));
    }

    #[Test]
    public function division_leader_can_view_division_requests_below_ssgt()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->assertTrue(RankActionPolicy::update($leader, $action));
    }

    #[Test]
    public function division_leader_can_view_staff_sergeant_requests()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::STAFF_SERGEANT,
        ]);

        $this->assertTrue(RankActionPolicy::update($leader, $action));
    }

    #[Test]
    public function division_leader_cannot_view_master_sergeant_requests()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::MASTER_SERGEANT,
        ]);

        $this->assertFalse(RankActionPolicy::update($leader, $action));
    }

    #[Test]
    public function platoon_leader_can_view_platoon_requests_below_their_rank()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::PLATOON_LEADER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->assertTrue(RankActionPolicy::update($leader, $action));
    }

    #[Test]
    public function platoon_leader_cannot_view_requests_equal_to_their_rank()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::PLATOON_LEADER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::STAFF_SERGEANT,
        ]);

        $this->assertFalse(RankActionPolicy::update($leader, $action));
    }

    #[Test]
    public function platoon_leader_cannot_view_requests_from_other_platoons()
    {
        $division = $this->createActiveDivision();
        $platoon1 = $this->createPlatoon($division);
        $platoon2 = $this->createPlatoon($division);

        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon1->id,
            'position'    => Position::PLATOON_LEADER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon2->id,
        ]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->assertFalse(RankActionPolicy::update($leader, $action));
    }

    #[Test]
    public function delete_any_requires_admin_role()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue(RankActionPolicy::deleteAny());
    }

    #[Test]
    public function non_admin_cannot_delete_any()
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);

        $this->assertFalse(RankActionPolicy::deleteAny());
    }

    #[Test]
    public function admin_can_approve_action_within_own_rank()
    {
        $admin  = $this->createAdmin();
        $member = $this->createMember(['division_id' => $admin->member->division_id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->assertTrue(RankActionPolicy::approve($admin, $action));
    }

    #[Test]
    public function user_cannot_approve_action_above_own_rank()
    {
        $division = $this->createActiveDivision();
        $leader   = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::SERGEANT,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::MASTER_SERGEANT,
        ]);

        $this->assertFalse(RankActionPolicy::approve($leader, $action));
    }

    #[Test]
    public function platoon_leader_can_approve_action_within_platoon_limit()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::PLATOON_LEADER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);
        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);

        // default max_platoon_leader_rank setting is Rank::PRIVATE_FIRST_CLASS
        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->assertTrue(RankActionPolicy::approve($leader, $action));
    }

    #[Test]
    public function platoon_leader_cannot_approve_action_from_other_platoon()
    {
        $division = $this->createActiveDivision();
        $platoon1 = $this->createPlatoon($division);
        $platoon2 = $this->createPlatoon($division);

        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon1->id,
            'position'    => Position::PLATOON_LEADER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);
        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon2->id,
        ]);

        // default max_platoon_leader_rank setting is Rank::PRIVATE_FIRST_CLASS
        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $this->assertFalse(RankActionPolicy::approve($leader, $action));
    }

    #[Test]
    public function command_sergeant_can_approve_sergeant_rank_action()
    {
        $division = $this->createActiveDivision();
        $leader   = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::COMMAND_SERGEANT,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::SERGEANT,
        ]);

        $this->assertTrue(RankActionPolicy::approve($leader, $action));
    }

    #[Test]
    public function master_sergeant_cannot_approve_sergeant_rank_action_without_leadership()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::MEMBER,
            'rank'        => Rank::MASTER_SERGEANT,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::SERGEANT,
        ]);

        $this->assertFalse(RankActionPolicy::approve($user, $action));
    }

    #[Test]
    public function master_sergeant_can_manage_comments_for_sergeant_action()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::MEMBER,
            'rank'        => Rank::MASTER_SERGEANT,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::SERGEANT,
        ]);

        $this->assertTrue(RankActionPolicy::manageComments($user, $action));
    }

    #[Test]
    public function staff_sergeant_cannot_manage_comments_for_sergeant_action_without_leadership()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::MEMBER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::SERGEANT,
        ]);

        $this->assertFalse(RankActionPolicy::manageComments($user, $action));
    }

    #[Test]
    public function division_leader_can_manage_comments_for_low_rank_action()
    {
        $division = $this->createActiveDivision();
        $leader   = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->assertTrue(RankActionPolicy::manageComments($leader, $action));
    }
}
