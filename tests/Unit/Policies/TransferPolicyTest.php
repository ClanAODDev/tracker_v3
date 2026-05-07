<?php

namespace Tests\Unit\Policies;

use App\Enums\Position;
use App\Models\Transfer;
use App\Policies\TransferPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function admin_bypasses_all_checks()
    {
        $admin = $this->createAdmin();

        $this->assertTrue($this->policy->before($admin));
    }

    #[Test]
    public function developer_bypasses_all_checks()
    {
        $division  = $this->createActiveDivision();
        $developer = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $developer->developer = true;
        $developer->save();

        $this->assertTrue($this->policy->before($developer));
    }

    #[Test]
    public function regular_user_does_not_bypass_checks()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertNull($this->policy->before($user));
    }

    #[Test]
    public function user_can_create_transfer_in_active_division()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertTrue($this->policy->create($user));
    }

    #[Test]
    public function user_cannot_create_transfer_in_inactive_division()
    {
        $division         = $this->createActiveDivision();
        $division->active = false;
        $division->save();

        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $user->refresh();

        $this->assertFalse($this->policy->create($user));
    }

    #[Test]
    public function division_leader_can_approve_transfer_to_their_division()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();
        $platoon      = $this->createPlatoon($toDivision);

        $leader = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->approve($leader, $transfer));
    }

    #[Test]
    public function division_leader_cannot_approve_transfer_to_other_division()
    {
        $fromDivision   = $this->createActiveDivision();
        $toDivision     = $this->createActiveDivision();
        $leaderDivision = $this->createActiveDivision();
        $platoon        = $this->createPlatoon($leaderDivision);

        $leader = $this->createMemberWithUser([
            'division_id' => $leaderDivision->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->approve($leader, $transfer));
    }

    #[Test]
    public function non_division_leader_cannot_approve_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();
        $platoon      = $this->createPlatoon($toDivision);

        $user = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::PLATOON_LEADER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->approve($user, $transfer));
    }

    #[Test]
    public function executive_officer_can_approve_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();
        $platoon      = $this->createPlatoon($toDivision);

        $xo = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::EXECUTIVE_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->approve($xo, $transfer));
    }

    #[Test]
    public function division_leader_can_delete_pending_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->delete($leader, $transfer));
    }

    #[Test]
    public function division_leader_cannot_delete_approved_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->approved()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->delete($leader, $transfer));
    }

    #[Test]
    public function division_leader_cannot_delete_transfer_to_other_division()
    {
        $fromDivision   = $this->createActiveDivision();
        $toDivision     = $this->createActiveDivision();
        $leaderDivision = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $leaderDivision->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->delete($leader, $transfer));
    }

    #[Test]
    public function losing_division_leader_can_hold_pending_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $fromDivision->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->hold($leader, $transfer));
    }

    #[Test]
    public function losing_division_leader_can_delete_pending_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $fromDivision->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->delete($leader, $transfer));
    }

    #[Test]
    public function gaining_division_leader_can_hold_pending_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $toDivision->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertTrue($this->policy->hold($leader, $transfer));
    }

    #[Test]
    public function cannot_hold_approved_transfer()
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $fromDivision->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->approved()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->hold($leader, $transfer));
    }

    #[Test]
    public function unrelated_division_leader_cannot_hold_transfer()
    {
        $fromDivision  = $this->createActiveDivision();
        $toDivision    = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();

        $leader = $this->createMemberWithUser([
            'division_id' => $otherDivision->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        $member = $this->createMember(['division_id' => $fromDivision->id]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertFalse($this->policy->hold($leader, $transfer));
    }
}
