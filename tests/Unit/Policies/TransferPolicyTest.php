<?php

namespace Tests\Unit\Policies;

use App\Enums\Position;
use App\Models\Transfer;
use App\Policies\TransferPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TransferPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private TransferPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TransferPolicy;
    }

    public function test_admin_bypasses_all_checks()
    {
        $admin = $this->createAdmin();

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

    public function test_user_can_create_transfer_in_active_division()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_cannot_create_transfer_in_inactive_division()
    {
        $division = $this->createActiveDivision();
        $division->active = false;
        $division->save();

        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $user->refresh();

        $this->assertFalse($this->policy->create($user));
    }

    public function test_division_leader_can_approve_transfer_to_their_division()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $platoon = $this->createPlatoon($toDivision);

        $leader = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'platoon_id' => $platoon->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->approve($leader, $transfer));
    }

    public function test_division_leader_cannot_approve_transfer_to_other_division()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $leaderDivision = $this->createActiveDivision();
        $platoon = $this->createPlatoon($leaderDivision);

        $leader = $this->createMemberWithUser([
            'division_id' => $leaderDivision->id,
            'platoon_id' => $platoon->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->approve($leader, $transfer));
    }

    public function test_non_division_leader_cannot_approve_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $platoon = $this->createPlatoon($toDivision);

        $user = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'platoon_id' => $platoon->id,
            'position' => Position::PLATOON_LEADER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->approve($user, $transfer));
    }

    public function test_executive_officer_can_approve_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $platoon = $this->createPlatoon($toDivision);

        $xo = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'platoon_id' => $platoon->id,
            'position' => Position::EXECUTIVE_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->approve($xo, $transfer));
    }

    public function test_division_leader_can_delete_pending_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->delete($leader, $transfer));
    }

    public function test_division_leader_cannot_delete_approved_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->approved()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->delete($leader, $transfer));
    }

    public function test_division_leader_cannot_delete_transfer_to_other_division()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision = $this->createActiveDivision();
        $leaderDivision = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $leaderDivision->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->delete($leader, $transfer));
    }
}
