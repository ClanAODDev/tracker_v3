<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AssignPlatoonAuthorizationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_assign_platoon()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $platoon  = $this->createPlatoon($division);

        $this->postJson(route('member.assign-platoon', $member->clan_id), [
            'platoon_id' => $platoon->id,
        ])->assertUnauthorized();
    }

    #[Test]
    public function regular_member_cannot_assign_platoon()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $target   = $this->createMember(['division_id' => $division->id]);
        $platoon  = $this->createPlatoon($division);

        $this->actingAs($user)
            ->postJson(route('member.assign-platoon', $target->clan_id), [
                'platoon_id' => $platoon->id,
            ])->assertForbidden();
    }

    #[Test]
    public function officer_can_assign_platoon()
    {
        $officer = $this->createOfficer();
        $target  = $this->createMember(['division_id' => $officer->member->division_id]);
        $platoon = $this->createPlatoon($officer->member->division);

        $this->actingAs($officer)
            ->postJson(route('member.assign-platoon', $target->clan_id), [
                'platoon_id' => $platoon->id,
            ])->assertOk();

        $this->assertEquals($platoon->id, $target->fresh()->platoon_id);
    }
}
