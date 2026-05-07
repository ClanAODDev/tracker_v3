<?php

namespace Tests\Unit\Policies;

use App\Enums\Role;
use App\Policies\LeavePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class LeavePolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private LeavePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new LeavePolicy;
    }

    #[Test]
    public function officer_can_create_leave()
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);

        $this->assertTrue($this->policy->create());
    }

    #[Test]
    public function sr_ldr_can_create_leave()
    {
        $srLdr = $this->createSeniorLeader();
        $this->actingAs($srLdr);

        $this->assertTrue($this->policy->create());
    }

    #[Test]
    public function admin_can_create_leave()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue($this->policy->create());
    }

    #[Test]
    public function member_cannot_create_leave()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();
        $this->actingAs($user);

        $this->assertFalse($this->policy->create());
    }

    #[Test]
    public function admin_can_update_leave()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue($this->policy->update());
    }

    #[Test]
    public function sr_ldr_can_update_leave()
    {
        $srLdr = $this->createSeniorLeader();
        $this->actingAs($srLdr);

        $this->assertTrue($this->policy->update());
    }

    #[Test]
    public function officer_cannot_update_leave()
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);

        $this->assertFalse($this->policy->update());
    }

    #[Test]
    public function member_cannot_update_leave()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();
        $this->actingAs($user);

        $this->assertFalse($this->policy->update());
    }

    #[Test]
    public function admin_can_delete_any_leave()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue($this->policy->deleteAny());
    }

    #[Test]
    public function sr_ldr_can_delete_any_leave()
    {
        $srLdr = $this->createSeniorLeader();
        $this->actingAs($srLdr);

        $this->assertTrue($this->policy->deleteAny());
    }

    #[Test]
    public function officer_cannot_delete_any_leave()
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);

        $this->assertFalse($this->policy->deleteAny());
    }

    #[Test]
    public function member_cannot_delete_any_leave()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role = Role::MEMBER;
        $user->save();
        $this->actingAs($user);

        $this->assertFalse($this->policy->deleteAny());
    }
}
