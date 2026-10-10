<?php

namespace Tests\Feature\Controllers;

use App\Enums\Position;
use App\Enums\Role;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitChildCreationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function a_manager_can_create_a_child_unit_from_the_unit_page(): void
    {
        $division = $this->createActiveDivision();
        $manager  = $this->createSeniorLeader($division);
        $platoon  = $this->createPlatoon($division);

        $this->actingAs($manager)
            ->post(route('unit.children.store', [$division->slug, $platoon]), ['name' => 'Alpha'])
            ->assertRedirect();

        $child = Unit::where('name', 'Alpha')->first();
        $this->assertSame($platoon->id, $child->parent_id);
        $this->assertSame($platoon->depth + 1, $child->depth);
    }

    #[Test]
    public function the_unit_page_offers_creation_only_to_managers_of_a_unit_with_a_child_level(): void
    {
        $division = $this->createActiveDivision();
        $manager  = $this->createSeniorLeader($division);
        $officer  = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::MEMBER], ['role' => Role::MEMBER]);
        $platoon  = $this->createPlatoon($division);

        $this->actingAs($manager)
            ->get(route('unit', [$division->slug, $platoon]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('scope.createChildUrl', route('unit.children.store', [$division->slug, $platoon])));

        $this->actingAs($officer)
            ->get(route('unit', [$division->slug, $platoon]))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('scope.createChildUrl', null));
    }

    #[Test]
    public function a_non_manager_cannot_create_a_child_unit(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::MEMBER], ['role' => Role::MEMBER]);
        $platoon  = $this->createPlatoon($division);

        $this->actingAs($officer)
            ->post(route('unit.children.store', [$division->slug, $platoon]), ['name' => 'Alpha'])
            ->assertForbidden();

        $this->assertDatabaseMissing('units', ['name' => 'Alpha']);
    }

    #[Test]
    public function a_unit_at_the_deepest_level_cannot_have_children(): void
    {
        $division = $this->createActiveDivision();
        $manager  = $this->createSeniorLeader($division);
        $squad    = $this->createSquad($this->createPlatoon($division));

        $this->actingAs($manager)
            ->post(route('unit.children.store', [$division->slug, $squad]), ['name' => 'Alpha'])
            ->assertForbidden();
    }

    #[Test]
    public function a_name_is_required(): void
    {
        $division = $this->createActiveDivision();
        $manager  = $this->createSeniorLeader($division);
        $platoon  = $this->createPlatoon($division);

        $this->actingAs($manager)
            ->post(route('unit.children.store', [$division->slug, $platoon]), ['name' => ''])
            ->assertSessionHasErrors('name');
    }
}
