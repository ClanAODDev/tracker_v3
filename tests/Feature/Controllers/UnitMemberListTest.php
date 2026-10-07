<?php

namespace Tests\Feature\Controllers;

use App\Enums\Position;
use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitMemberListTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function platoon_show_renders_the_members_inertia_page()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $platoon  = $this->createPlatoon($division);
        $member   = $this->createMember(['division_id' => $division->id, 'unit_id' => $platoon->id]);

        $this->actingAs($officer)
            ->get(route('unit', [$division->slug, $platoon]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/members')
                ->where('scope.kind', 'platoon')
                ->where('assignmentKind', 'squad')
                ->where('members', fn ($members) => collect($members)->contains('id', $member->clan_id))
                ->has('squads'));
    }

    #[Test]
    public function manage_squads_renders_the_drag_and_drop_page_for_a_platoon_manager()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $platoon  = $this->createPlatoonWithSquads($division);
        $squad    = $platoon->children->first();
        $assigned = $this->createMember([
            'division_id' => $division->id,
            'unit_id'     => $squad->id,
        ]);
        $floating = $this->createMember([
            'division_id' => $division->id,
            'unit_id'     => $platoon->id,
        ]);

        $this->actingAs($srLdr)
            ->get(route('unit.manage', [$division->slug, $platoon]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('platoon/manage-members')
                ->where('squads', fn ($squads) => collect($squads)
                    ->firstWhere('id', $squad->id)['members'][0]['id'] === $assigned->id)
                ->where('unassigned', fn ($unassigned) => collect($unassigned)->contains('id', $floating->id))
                ->has('assignUrl'));
    }

    #[Test]
    public function manage_squads_is_forbidden_without_a_leadership_position()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $user     = $this->createMemberWithUser(
            ['division_id' => $division->id, 'position' => Position::MEMBER],
            ['role' => Role::OFFICER],
        );

        $this->actingAs($user)
            ->get(route('unit.manage', [$division->slug, $platoon]))
            ->assertForbidden();
    }

    #[Test]
    public function squad_show_renders_the_members_inertia_page()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $member   = $this->createMember([
            'division_id' => $division->id,
            'unit_id'     => $squad->id,
        ]);

        $this->actingAs($officer)
            ->get(route('unit', [$division->slug, $squad]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/members')
                ->where('scope.kind', 'squad')
                ->where('members', fn ($members) => collect($members)->contains('id', $member->clan_id)));
    }
}
