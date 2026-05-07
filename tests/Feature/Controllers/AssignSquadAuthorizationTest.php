<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AssignSquadAuthorizationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_user_cannot_assign_squad()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $squad    = $this->createSquad($this->createPlatoon($division));

        $this->postJson('/members/assign-squad', [
            'member_id' => $member->id,
            'squad_id'  => $squad->id,
        ])->assertUnauthorized();
    }

    #[Test]
    public function regular_member_cannot_assign_squad()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $target   = $this->createMember(['division_id' => $division->id]);
        $squad    = $this->createSquad($this->createPlatoon($division));

        $this->actingAs($user)
            ->postJson('/members/assign-squad', [
                'member_id' => $target->id,
                'squad_id'  => $squad->id,
            ])->assertForbidden();
    }

    #[Test]
    public function officer_can_assign_squad()
    {
        $officer = $this->createOfficer();
        $target  = $this->createMember(['division_id' => $officer->member->division_id]);
        $platoon = $this->createPlatoon($officer->member->division);
        $squad   = $this->createSquad($platoon);

        $this->actingAs($officer)
            ->postJson('/members/assign-squad', [
                'member_id' => $target->id,
                'squad_id'  => $squad->id,
            ])->assertOk();

        $target->refresh();
        $this->assertEquals($squad->id, $target->squad_id);
        $this->assertEquals($platoon->id, $target->platoon_id);
    }
}
