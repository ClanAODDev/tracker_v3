<?php

namespace Tests\Feature;

use App\Data\PendingActionsData;
use App\Models\Division;
use App\Models\DivisionUnitLevel;
use App\Models\Member;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class LeadersAcrossLevelsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private Division $division;

    private Unit $platoon;

    private Unit $squad;

    private Unit $team;

    private User $commander;

    private Member $platoonLeader;

    private Member $squadLeader;

    private Member $teamMember;

    private Member $memberInPlatoon;

    private Member $memberInSquad;

    private Member $memberInDivision;

    protected function setUp(): void
    {
        parent::setUp();

        app('router')->get('/mod/member-requests', fn () => null)->name('filament.mod.resources.member-requests.index');
        app('router')->getRoutes()->refreshNameLookups();

        $this->division = $this->createActiveDivision();
        DivisionUnitLevel::where('division_id', $this->division->id)->where('depth', 1)->update(['leader_title' => 'Platoon Leader']);
        DivisionUnitLevel::where('division_id', $this->division->id)->where('depth', 2)->update(['leader_title' => 'Squad Leader']);
        DivisionUnitLevel::create(['division_id' => $this->division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);
        $this->division = $this->division->fresh();

        $this->platoon = $this->createPlatoon($this->division);
        $this->squad   = $this->createSquad($this->platoon);
        $this->team    = Unit::factory()->childOf($this->squad)->create();

        $this->commander        = $this->createSeniorLeader($this->division);
        $this->platoonLeader    = $this->createPlatoonLeader($this->platoon);
        $this->squadLeader      = $this->createSquadLeader($this->squad);
        $this->teamMember       = $this->createMember(['division_id' => $this->division->id, 'unit_id' => $this->team->id]);
        $this->memberInPlatoon  = $this->createMember(['division_id' => $this->division->id, 'unit_id' => $this->platoon->id]);
        $this->memberInSquad    = $this->createMember(['division_id' => $this->division->id, 'unit_id' => $this->squad->id]);
        $this->memberInDivision = $this->createMember(['division_id' => $this->division->id, 'unit_id' => null]);
    }

    #[Test]
    public function leaders_above_the_bottom_level_are_not_reported_as_unplaced(): void
    {
        $this->actingAs($this->commander);

        $actions = PendingActionsData::forDivision($this->division, $this->commander);

        $this->assertSame(1, $actions->get('unassigned-members')->count);
        $this->assertSame(1, $actions->get('unassigned-to-squad')->count);
        $this->assertSame(1, $actions->get('unassigned-to-level-3')->count);
    }

    #[Test]
    public function a_unit_roster_rolls_up_everyone_beneath_it(): void
    {
        $platoonRoster = $this->platoon->allMembers()->pluck('clan_id');
        $squadRoster   = $this->squad->allMembers()->pluck('clan_id');

        $this->assertEqualsCanonicalizing(
            collect([$this->platoonLeader, $this->squadLeader, $this->teamMember, $this->memberInPlatoon, $this->memberInSquad])
                ->map(fn (Member $member) => $member->clan_id)
                ->all(),
            $platoonRoster->all(),
        );

        $this->assertEqualsCanonicalizing(
            collect([$this->squadLeader, $this->teamMember, $this->memberInSquad])
                ->map(fn (Member $member) => $member->clan_id)
                ->all(),
            $squadRoster->all(),
        );

        $this->assertNotContains($this->commander->member->clan_id, $platoonRoster->all());
    }

    #[Test]
    public function each_leader_is_titled_for_the_level_they_lead(): void
    {
        $this->assertSame('Platoon Leader', $this->platoonLeader->fresh()->positionLabel());
        $this->assertSame('Squad Leader', $this->squadLeader->fresh()->positionLabel());
        $this->assertSame('Commanding Officer', $this->commander->member->fresh()->positionLabel());
    }

    #[Test]
    public function the_org_chart_places_each_leader_on_the_unit_they_lead(): void
    {
        $tree = $this->actingAs($this->commander)
            ->getJson(route('division.structure.data', $this->division->slug))
            ->assertOk()
            ->json();

        $platoonNode = $this->findNode($tree, "platoon-{$this->platoon->id}");
        $squadNode   = $this->findNode($tree, "platoon-{$this->squad->id}");

        $this->assertSame($this->platoonLeader->name, $platoonNode['leader']['name']);
        $this->assertSame($this->squadLeader->name, $squadNode['leader']['name']);
        $this->assertNull($this->findNode($tree, "squad-{$this->platoon->id}"));
    }

    #[Test]
    public function the_org_chart_lists_members_placed_directly_in_a_higher_level_unit(): void
    {
        $tree = $this->actingAs($this->commander)
            ->getJson(route('division.structure.data', $this->division->slug))
            ->json();

        $this->assertNotNull($this->findNode($tree, "member-{$this->memberInPlatoon->clan_id}"));
        $this->assertNotNull($this->findNode($tree, "member-{$this->memberInSquad->clan_id}"));
        $this->assertNotNull($this->findNode($tree, "member-{$this->teamMember->clan_id}"));
    }

    private function findNode(array $node, string $id): ?array
    {
        if (($node['id'] ?? null) === $id) {
            return $node;
        }

        foreach ($node['children'] ?? [] as $child) {
            if ($found = $this->findNode($child, $id)) {
                return $found;
            }
        }

        return null;
    }
}
