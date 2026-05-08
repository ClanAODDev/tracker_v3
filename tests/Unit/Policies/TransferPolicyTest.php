<?php

namespace Tests\Unit\Policies;

use App\Enums\Position;
use App\Models\Transfer;
use App\Policies\TransferPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public static function approveProvider(): array
    {
        return [
            'co in target division'      => [Position::COMMANDING_OFFICER, true,  true],
            'co in different division'   => [Position::COMMANDING_OFFICER, false, false],
            'platoon leader in division' => [Position::PLATOON_LEADER,     true,  false],
            'xo in target division'      => [Position::EXECUTIVE_OFFICER,  true,  true],
        ];
    }

    #[Test]
    #[DataProvider('approveProvider')]
    public function approve_authorization(Position $position, bool $userInTargetDivision, bool $expected): void
    {
        $fromDivision = $this->createActiveDivision();
        $toDivision   = $this->createActiveDivision();
        $userDivision = $userInTargetDivision ? $toDivision : $this->createActiveDivision();
        $platoon      = $this->createPlatoon($userDivision);

        $user = $this->createMemberWithUser([
            'division_id' => $userDivision->id,
            'platoon_id'  => $platoon->id,
            'position'    => $position,
        ]);

        $member   = $this->createMember(['division_id' => $fromDivision->id]);
        $transfer = Transfer::factory()->pending()->create([
            'member_id'   => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->assertSame($expected, $this->policy->approve($user, $transfer));
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
