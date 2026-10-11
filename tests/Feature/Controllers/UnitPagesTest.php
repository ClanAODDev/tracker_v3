<?php

namespace Tests\Feature\Controllers;

use App\Services\Units\UnitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitPagesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function a_unit_is_only_reachable_through_its_own_division(): void
    {
        $officer = $this->createOfficer();
        $other   = $this->createActiveDivision();
        $unit    = $this->createPlatoon($other);

        $this->actingAs($officer)
            ->get(route('unit', [$officer->member->division->slug, $unit]))
            ->assertNotFound();

        $this->get(route('unit', [$other->slug, $unit]))->assertOk();
    }

    #[Test]
    public function archived_units_are_not_found(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $unit     = $this->createPlatoon($division);
        $this->actingAs($this->createAdmin());
        app(UnitAssignment::class)->archive($unit);

        $this->actingAs($officer)->get(route('unit', [$division->slug, $unit->id]))->assertNotFound();
    }

    #[Test]
    public function the_inactive_members_page_filters_by_platoon_unit(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $alpha    = $this->createPlatoon($division);
        $bravo    = $this->createPlatoon($division);
        $inAlpha  = $this->createMember(['division_id' => $division->id, 'unit_id' => $this->createSquad($alpha)->id, 'last_voice_activity' => now()->subYear()]);
        $this->createMember(['division_id' => $division->id, 'unit_id' => $bravo->id, 'last_voice_activity' => now()->subYear()]);

        $this->actingAs($officer)
            ->get(route('division.inactive-members', [$division->slug, $alpha]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('activePlatoon', $alpha->id)
                ->where('inactive', fn ($rows) => collect($rows)->pluck('name')->contains(fn ($name) => str_contains($name, $inAlpha->name)) && count($rows) === 1)
                ->where('platoons', fn ($platoons) => collect($platoons)->firstWhere('id', $alpha->id)['count'] === 1));
    }
}
