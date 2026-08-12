<?php

namespace Tests\Feature\Controllers;

use App\Enums\Position;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberAssignmentAuthorizationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function unassign_member_is_forbidden_for_member_role()
    {
        $user   = $this->createMemberWithUser();
        $target = $this->createMember();

        $this->actingAs($user)
            ->post(route('member.unassign', $target->getUrlParams()))
            ->assertForbidden();
    }

    #[Test]
    public function unassign_member_is_forbidden_for_self()
    {
        $srLdr = $this->createSeniorLeader();

        $this->actingAs($srLdr)
            ->post(route('member.unassign', $srLdr->member->getUrlParams()))
            ->assertForbidden();
    }

    #[Test]
    public function unassign_member_is_permitted_for_sr_ldr_on_another_member()
    {
        $srLdr  = $this->createSeniorLeader();
        $target = $this->createMember(['platoon_id' => 5, 'squad_id' => 9]);

        $this->actingAs($srLdr)
            ->post(route('member.unassign', $target->getUrlParams()))
            ->assertRedirect();

        $this->assertSame(0, $target->fresh()->platoon_id);
        $this->assertSame(0, $target->fresh()->squad_id);
    }

    #[Test]
    public function assign_squad_is_forbidden_for_officer_without_leadership_position()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoonWithSquads($division);
        $squad    = $platoon->squads->first();
        $officer  = $this->createMemberWithUser(
            ['division_id' => $division->id, 'position' => Position::MEMBER],
            ['role' => Role::OFFICER]
        );
        $target = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer)
            ->post('/members/assign-squad', [
                'member_id' => $target->id,
                'squad_id'  => $squad->id,
            ])
            ->assertForbidden();

        $this->assertNotEquals($squad->id, $target->fresh()->squad_id);
    }

    #[Test]
    public function assign_squad_is_permitted_for_sr_ldr_of_the_platoons_division()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoonWithSquads($division);
        $squad    = $platoon->squads->first();
        $srLdr    = $this->createSeniorLeader($division);
        $target   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($srLdr)
            ->post('/members/assign-squad', [
                'member_id' => $target->id,
                'squad_id'  => $squad->id,
            ])
            ->assertOk();

        $this->assertSame($squad->id, $target->fresh()->squad_id);
    }

    #[Test]
    public function assign_squad_is_forbidden_for_sr_ldr_of_a_different_division()
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $platoon       = $this->createPlatoonWithSquads($division);
        $squad         = $platoon->squads->first();
        $srLdr         = $this->createSeniorLeader($otherDivision);
        $target        = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($srLdr)
            ->post('/members/assign-squad', [
                'member_id' => $target->id,
                'squad_id'  => $squad->id,
            ])
            ->assertForbidden();
    }

    #[Test]
    public function assign_platoon_is_permitted_for_sr_ldr_within_same_division()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $srLdr    = $this->createSeniorLeader($division);
        $target   = $this->createMember(['division_id' => $division->id, 'platoon_id' => 0]);

        $this->actingAs($srLdr)
            ->post(route('member.assign-platoon', $target->getUrlParams()), [
                'platoon_id' => $platoon->id,
            ])
            ->assertOk();

        $this->assertSame($platoon->id, $target->fresh()->platoon_id);
    }

    #[Test]
    public function assign_platoon_rejects_platoon_from_another_division()
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $otherPlatoon  = $this->createPlatoon($otherDivision);
        $srLdr         = $this->createSeniorLeader($division);
        $target        = $this->createMember(['division_id' => $division->id, 'platoon_id' => 0]);

        $this->actingAs($srLdr)
            ->post(route('member.assign-platoon', $target->getUrlParams()), [
                'platoon_id' => $otherPlatoon->id,
            ])
            ->assertNotFound();

        $this->assertSame(0, $target->fresh()->platoon_id);
    }

    #[Test]
    public function clear_activity_reminders_is_forbidden_for_member_role()
    {
        $user   = $this->createMemberWithUser();
        $target = $this->createMember();

        $this->actingAs($user)
            ->delete(route('member.clear-activity-reminders', $target->getUrlParams()))
            ->assertForbidden();
    }

    #[Test]
    public function clear_activity_reminders_is_forbidden_for_officer_role()
    {
        $officer = $this->createOfficer();
        $target  = $this->createMember();

        $this->actingAs($officer)
            ->delete(route('member.clear-activity-reminders', $target->getUrlParams()))
            ->assertForbidden();
    }

    #[Test]
    public function clear_activity_reminders_is_forbidden_for_self()
    {
        $srLdr = $this->createSeniorLeader();

        $this->actingAs($srLdr)
            ->delete(route('member.clear-activity-reminders', $srLdr->member->getUrlParams()))
            ->assertForbidden();
    }

    #[Test]
    public function clear_activity_reminders_is_permitted_for_sr_ldr_on_another_member()
    {
        $srLdr  = $this->createSeniorLeader();
        $target = $this->createMember();

        $this->actingAs($srLdr)
            ->delete(route('member.clear-activity-reminders', $target->getUrlParams()))
            ->assertOk();
    }

    #[Test]
    public function unassigned_to_squad_is_forbidden_for_officer_role()
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createMemberWithUser(
            ['division_id' => $division->id, 'position' => Position::MEMBER],
            ['role' => Role::OFFICER]
        );

        $this->actingAs($officer)
            ->get(route('division.unassigned-to-squad', $division->slug))
            ->assertForbidden();
    }

    #[Test]
    public function unassigned_to_squad_is_permitted_for_sr_ldr()
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);

        $this->actingAs($srLdr)
            ->get(route('division.unassigned-to-squad', $division->slug))
            ->assertOk();
    }
}
