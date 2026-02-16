<?php

namespace Tests\Unit\Policies;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Models\RankAction;
use App\Policies\RankActionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RankActionPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_admin_can_view_any_rank_action()
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

    public function test_user_cannot_view_own_rank_action()
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

    public function test_requester_can_view_their_request()
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

    public function test_division_leader_can_view_division_requests_below_ssgt()
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

    public function test_division_leader_can_view_staff_sergeant_requests()
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

    public function test_division_leader_cannot_view_master_sergeant_requests()
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

    public function test_platoon_leader_can_view_platoon_requests_below_their_rank()
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

    public function test_platoon_leader_cannot_view_requests_equal_to_their_rank()
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

    public function test_platoon_leader_cannot_view_requests_from_other_platoons()
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

    public function test_delete_any_requires_admin_role()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue(RankActionPolicy::deleteAny());
    }

    public function test_non_admin_cannot_delete_any()
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);

        $this->assertFalse(RankActionPolicy::deleteAny());
    }
}
