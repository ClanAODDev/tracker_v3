<?php

namespace Tests\Unit\Policies;

use App\Policies\LeavePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_officer_can_create_leave()
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);

        $this->assertTrue($this->policy->create());
    }

    public function test_sr_ldr_can_create_leave()
    {
        $srLdr = $this->createSeniorLeader();
        $this->actingAs($srLdr);

        $this->assertTrue($this->policy->create());
    }

    public function test_admin_can_create_leave()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue($this->policy->create());
    }

    public function test_member_cannot_create_leave()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role_id = 1;
        $user->save();
        $this->actingAs($user);

        $this->assertFalse($this->policy->create());
    }

    public function test_admin_can_update_leave()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue($this->policy->update());
    }

    public function test_sr_ldr_can_update_leave()
    {
        $srLdr = $this->createSeniorLeader();
        $this->actingAs($srLdr);

        $this->assertTrue($this->policy->update());
    }

    public function test_officer_cannot_update_leave()
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);

        $this->assertFalse($this->policy->update());
    }

    public function test_member_cannot_update_leave()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role_id = 1;
        $user->save();
        $this->actingAs($user);

        $this->assertFalse($this->policy->update());
    }

    public function test_admin_can_delete_any_leave()
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->assertTrue($this->policy->deleteAny());
    }

    public function test_sr_ldr_can_delete_any_leave()
    {
        $srLdr = $this->createSeniorLeader();
        $this->actingAs($srLdr);

        $this->assertTrue($this->policy->deleteAny());
    }

    public function test_officer_cannot_delete_any_leave()
    {
        $officer = $this->createOfficer();
        $this->actingAs($officer);

        $this->assertFalse($this->policy->deleteAny());
    }

    public function test_member_cannot_delete_any_leave()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
        ]);
        $user->role_id = 1;
        $user->save();
        $this->actingAs($user);

        $this->assertFalse($this->policy->deleteAny());
    }
}
