<?php

namespace Tests\Feature\Controllers;

use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionOrgChartControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function structure_renders_the_org_chart_with_a_tree(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->actingAs($officer)
            ->get(route('division.structure', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/org-chart')
                ->where('division.slug', $division->slug)
                ->where('tree.type', 'division')
                ->has('tree.children'));
    }

    #[Test]
    public function data_endpoint_still_returns_json(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->actingAs($officer)
            ->getJson(route('division.structure.data', $division->slug))
            ->assertOk()
            ->assertJsonPath('type', 'division');
    }

    #[Test]
    public function a_squad_leader_is_not_repeated_among_their_squads_members(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $squad    = $this->createSquad($this->createPlatoon($division));
        $leader   = $this->createSquadLeader($squad, ['name' => 'Squad Boss']);
        $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id, 'name' => 'Squad Grunt']);

        $tree      = $this->actingAs($officer)->getJson(route('division.structure.data', $division->slug))->json();
        $squadNode = collect($tree['children'])->firstWhere('type', 'platoon')['children'][0];

        $this->assertSame(['Squad Grunt'], collect($squadNode['children'])->pluck('name')->all());
        $this->assertNotNull($leader);
    }

    #[Test]
    public function units_nest_to_every_configured_level(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);
        $company = $this->createPlatoon($division);
        $middle  = Unit::factory()->childOf($company)->create();
        $team    = Unit::factory()->childOf($middle)->create();
        $this->createMember(['division_id' => $division->id, 'unit_id' => $team->id, 'name' => 'Team Grunt']);

        $tree        = $this->actingAs($officer)->getJson(route('division.structure.data', $division->slug))->json();
        $companyNode = collect($tree['children'])->firstWhere('id', "platoon-{$company->id}");
        $middleNode  = $companyNode['children'][0];
        $teamNode    = $middleNode['children'][0];

        $this->assertSame('platoon', $middleNode['type']);
        $this->assertSame('squad', $teamNode['type']);
        $this->assertSame(['Team Grunt'], collect($teamNode['children'])->pluck('name')->all());
    }

    #[Test]
    public function a_division_with_one_unit_level_shows_its_units_with_their_leader_title(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        DivisionUnitLevel::where('division_id', $division->id)->where('depth', 2)->delete();
        $platoon = $this->createPlatoon($division, ['name' => 'First Platoon']);
        $leader  = $this->createPlatoonLeader($platoon, ['division_id' => $division->id]);
        $this->createMember(['division_id' => $division->id, 'unit_id' => $platoon->id, 'name' => 'Platoon Grunt']);

        $tree = $this->actingAs($officer)->getJson(route('division.structure.data', $division->slug))->json();
        $node = collect($tree['children'])->firstWhere('id', "platoon-{$platoon->id}");

        $this->assertSame('platoon', $node['type']);
        $this->assertSame($division->fresh()->unitLevel(1)->leader_title, $node['leaderTitle']);
        $this->assertSame($leader->name, $node['leader']['name']);
        $this->assertContains('Platoon Grunt', collect($node['children'])->flatMap(fn ($child) => collect($child['children'] ?? [])->pluck('name'))->all());
    }
}
