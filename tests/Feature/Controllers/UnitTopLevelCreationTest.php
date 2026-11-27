<?php

namespace Tests\Feature\Controllers;

use App\Enums\Position;
use App\Enums\Role;
use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitTopLevelCreationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function a_division_leader_can_create_a_top_level_unit_from_the_division_page(): void
    {
        $division = $this->createActiveDivision();
        $manager  = $this->createSeniorLeader($division);

        $this->actingAs($manager)
            ->post(route('unit.store', $division->slug), ['name' => 'First Platoon'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $unit = Unit::where('name', 'First Platoon')->first();
        $this->assertSame($division->id, $unit->division_id);
        $this->assertNull($unit->parent_id);
        $this->assertSame(1, $unit->depth);
        $this->assertSame("/{$unit->id}/", $unit->path);
    }

    #[Test]
    public function a_name_is_required(): void
    {
        $division = $this->createActiveDivision();

        $this->actingAs($this->createSeniorLeader($division))
            ->post(route('unit.store', $division->slug), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, Unit::where('division_id', $division->id)->count());
    }

    #[Test]
    public function members_without_unit_management_cannot_create_one(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::MEMBER], ['role' => Role::MEMBER]);

        $this->actingAs($member)
            ->post(route('unit.store', $division->slug), ['name' => 'Rogue'])
            ->assertForbidden();

        $this->assertFalse(Unit::where('name', 'Rogue')->exists());
    }

    #[Test]
    public function leaders_of_another_division_cannot_create_one(): void
    {
        $division = $this->createActiveDivision();
        $other    = $this->createActiveDivision();

        $this->actingAs($this->createSeniorLeader($other))
            ->post(route('unit.store', $division->slug), ['name' => 'Outsider'])
            ->assertForbidden();
    }

    #[Test]
    public function a_flat_division_cannot_get_units(): void
    {
        $division = $this->createActiveDivision();
        DivisionUnitLevel::where('division_id', $division->id)->delete();

        $this->actingAs($this->createSeniorLeader($division->fresh()))
            ->post(route('unit.store', $division->slug), ['name' => 'Nope'])
            ->assertForbidden();
    }

    #[Test]
    public function the_division_page_offers_creation_only_to_those_who_can_create(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::MEMBER], ['role' => Role::MEMBER]);

        $this->actingAs($this->createSeniorLeader($division))
            ->get(route('division', $division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('division.createUnitUrl', route('unit.store', $division->slug)));

        $this->actingAs($member)
            ->get(route('division', $division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('division.createUnitUrl', null));
    }
}
