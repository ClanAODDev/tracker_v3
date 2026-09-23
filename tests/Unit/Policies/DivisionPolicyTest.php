<?php

namespace Tests\Unit\Policies;

use App\Enums\Role;
use App\Policies\DivisionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private DivisionPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new DivisionPolicy;
    }

    #[Test]
    public function admin_cannot_delete_division()
    {
        $admin    = $this->createAdmin(userAttributes: ['developer' => false]);
        $division = $this->createActiveDivision();

        $this->assertFalse($this->policy->delete($admin, $division));
    }

    #[Test]
    public function admin_cannot_delete_any_division()
    {
        $admin = $this->createAdmin(userAttributes: ['developer' => false]);

        $this->assertFalse($this->policy->deleteAny($admin));
    }

    #[Test]
    public function division_leader_without_sr_ldr_role_can_update_their_division(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);

        $this->assertTrue($this->policy->update($officer, $division));
    }

    #[Test]
    public function sr_ldr_role_without_division_leader_position_cannot_update_division(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMemberWithUser(
            ['division_id' => $division->id],
            ['role' => Role::SENIOR_LEADER],
        );

        $this->assertFalse($this->policy->update($member, $division));
    }

    #[Test]
    public function division_leader_cannot_update_a_different_division(): void
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $officer       = $this->createOfficer($division);

        $this->assertFalse($this->policy->update($officer, $otherDivision));
    }
}
