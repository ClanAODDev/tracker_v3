<?php

namespace Tests\Unit\Policies;

use App\Policies\SquadPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class SquadPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function officer_can_view_any_squad()
    {
        $officer = $this->createOfficer();

        $this->assertTrue(SquadPolicy::viewAny($officer));
    }

    #[Test]
    public function sr_ldr_can_view_any_squad()
    {
        $srLdr = $this->createSeniorLeader();

        $this->assertTrue(SquadPolicy::viewAny($srLdr));
    }

    #[Test]
    public function regular_member_cannot_view_any_squad()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->assertFalse(SquadPolicy::viewAny($user));
    }
}
