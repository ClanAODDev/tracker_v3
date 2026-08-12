<?php

namespace Tests\Unit\Policies;

use App\Policies\PlatoonPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class PlatoonPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private PlatoonPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PlatoonPolicy;
    }

    #[Test]
    public function officer_can_view_any_platoon()
    {
        $officer = $this->createOfficer();

        $this->assertTrue($this->policy->viewAny($officer));
    }

    #[Test]
    public function sr_ldr_can_view_any_platoon()
    {
        $srLdr = $this->createSeniorLeader();

        $this->assertTrue($this->policy->viewAny($srLdr));
    }

    #[Test]
    public function regular_member_cannot_view_any_platoon()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertFalse($this->policy->viewAny($user));
    }
}
