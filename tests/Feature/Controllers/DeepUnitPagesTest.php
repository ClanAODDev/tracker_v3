<?php

namespace Tests\Feature\Controllers;

use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DeepUnitPagesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private User $officer;

    private Unit $company;

    private Unit $middle;

    private Unit $leaf;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officer = $this->createOfficer();
        $division      = $this->officer->member->division;
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);

        $this->company = $this->createPlatoon($division);
        $this->middle  = Unit::factory()->childOf($this->company)->create();
        $this->leaf    = Unit::factory()->childOf($this->middle)->create();
    }

    #[Test]
    public function a_middle_unit_lists_its_children_and_counts_their_subtrees(): void
    {
        $division = $this->officer->member->division;
        $this->createMember(['division_id' => $division->id, 'unit_id' => $this->leaf->id]);
        $this->createMember(['division_id' => $division->id, 'unit_id' => $this->middle->id]);

        $this->actingAs($this->officer)
            ->get(route('unit', [$division->slug, $this->company]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('scope.kind', 'platoon')
                ->where('scope.platoonLabel', 'Platoon')
                ->where('scope.squadLabel', 'Squad')
                ->has('squads', 1)
                ->where('squads.0.id', $this->middle->id)
                ->where('squads.0.count', 2));
    }

    #[Test]
    public function a_leaf_unit_shows_the_full_ancestry_in_its_breadcrumbs(): void
    {
        $division = $this->officer->member->division;

        $this->actingAs($this->officer)
            ->get(route('unit', [$division->slug, $this->leaf]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('scope.kind', 'squad')
                ->where('scope.squadLabel', 'Team')
                ->has('scope.breadcrumbs', 4)
                ->where('scope.breadcrumbs.1.href', $this->company->url($division))
                ->where('scope.breadcrumbs.2.href', $this->middle->url($division)));
    }

    #[Test]
    public function a_middle_unit_has_a_manage_page_for_its_children(): void
    {
        $division = $this->officer->member->division;
        $this->createMember(['division_id' => $division->id, 'unit_id' => $this->leaf->id]);

        $this->actingAs($this->officer)
            ->get(route('unit.manage', [$division->slug, $this->middle]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('division.squadLabel', 'Team')
                ->where('platoon.id', $this->middle->id)
                ->has('squads', 1)
                ->has('squads.0.members', 1)
                ->has('breadcrumbs', 3));
    }

    #[Test]
    public function members_can_be_assigned_to_a_unit_at_any_depth(): void
    {
        $division = $this->officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id, 'unit_id' => $this->middle->id]);

        $this->actingAs($this->officer)
            ->postJson('/members/assign-squad', ['member_id' => $member->id, 'unit_id' => $this->leaf->id])
            ->assertOk();

        $this->assertSame($this->leaf->id, $member->fresh()->unit_id);

        $this->postJson('/members/assign-squad', ['member_id' => $member->id, 'unit_id' => $this->middle->id])
            ->assertOk();

        $this->assertSame($this->middle->id, $member->fresh()->unit_id);
    }

    #[Test]
    public function members_cannot_be_assigned_to_a_unit_in_another_division(): void
    {
        $other  = $this->createPlatoon($this->createActiveDivision());
        $member = $this->createMember(['division_id' => $this->officer->member->division_id]);

        $this->actingAs($this->officer)
            ->postJson('/members/assign-squad', ['member_id' => $member->id, 'unit_id' => $other->id])
            ->assertUnprocessable();
    }
}
