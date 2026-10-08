<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use App\Filament\Mod\Resources\DivisionResource\RelationManagers\PlatoonsRelationManager;
use App\Filament\Mod\Resources\UnitResource;
use App\Models\Division;
use App\Models\DivisionUnitLevel;
use App\Services\Units\UnitAssignment;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class FlatDivisionTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('mod');
    }

    #[Test]
    public function every_level_can_be_removed_when_no_units_exist(): void
    {
        [$division, $co] = $this->divisionWithCommander();
        $this->actingAs($co);

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->set('data.unitLevels', [])
            ->call('save');

        $this->assertSame(0, DivisionUnitLevel::where('division_id', $division->id)->count());
        $this->assertTrue($division->fresh()->isFlat());
    }

    #[Test]
    public function a_default_division_is_not_flat(): void
    {
        $division = $this->createActiveDivision();

        $this->assertFalse($division->isFlat());
        $this->assertSame(2, $division->deepestUnitLevel());
    }

    #[Test]
    public function units_cannot_be_created_in_a_flat_division(): void
    {
        $division = $this->flatDivision();

        $this->expectException(\InvalidArgumentException::class);

        app(UnitAssignment::class)->create($division, null, ['name' => 'Nowhere']);
    }

    #[Test]
    public function the_unit_screens_are_hidden_for_a_flat_division(): void
    {
        [$division, $co] = $this->divisionWithCommander();
        $this->actingAs($co);

        $this->assertTrue(UnitResource::shouldRegisterNavigation());
        $this->assertTrue(PlatoonsRelationManager::canViewForRecord($division, EditDivision::class));

        DivisionUnitLevel::where('division_id', $division->id)->delete();
        $co->member->unsetRelation('division');

        $this->assertFalse(UnitResource::shouldRegisterNavigation());
        $this->assertFalse(PlatoonsRelationManager::canViewForRecord($division->fresh(), EditDivision::class));
    }

    #[Test]
    public function the_division_page_omits_units_and_the_no_platoon_action(): void
    {
        $division = $this->flatDivision();
        $co       = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::COMMANDING_OFFICER], ['role' => Role::SENIOR_LEADER]);
        $this->createMember(['division_id' => $division->id, 'unit_id' => null]);

        $this->actingAs($co)
            ->get(route('division', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('division.hasUnits', false)
                ->where('pendingActions', fn ($actions) => collect($actions)->doesntContain('key', 'unassigned-members')));
    }

    #[Test]
    public function a_flat_divisions_page_lists_every_member(): void
    {
        $division = $this->flatDivision();
        $co       = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::COMMANDING_OFFICER], ['role' => Role::SENIOR_LEADER]);
        $this->createMember(['division_id' => $division->id, 'unit_id' => null]);
        $this->createMember(['division_id' => $division->id, 'unit_id' => null]);

        $this->actingAs($co)
            ->get(route('division', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('memberList.members', 3)
                ->where('memberList.division.hasUnits', false));
    }

    #[Test]
    public function a_division_with_units_leaves_the_member_list_to_its_own_page(): void
    {
        [$division, $co] = $this->divisionWithCommander();

        $this->actingAs($co)
            ->get(route('division', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->missing('memberList'));
    }

    #[Test]
    public function the_org_chart_of_a_flat_division_lists_its_members(): void
    {
        $division = $this->flatDivision();
        $co       = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::COMMANDING_OFFICER], ['role' => Role::SENIOR_LEADER]);
        $this->createMember(['division_id' => $division->id]);
        $this->createMember(['division_id' => $division->id]);

        $tree = $this->actingAs($co)
            ->getJson(route('division.structure.data', $division->slug))
            ->assertOk()
            ->json();

        $roster = collect($tree['children'])->firstWhere('id', 'roster');

        $this->assertCount(2, $roster['children']);
    }

    #[Test]
    public function the_org_chart_of_a_division_with_units_has_no_roster(): void
    {
        [$division, $co] = $this->divisionWithCommander();

        $tree = $this->actingAs($co)
            ->getJson(route('division.structure.data', $division->slug))
            ->json();

        $this->assertNull(collect($tree['children'])->firstWhere('id', 'roster'));
    }

    #[Test]
    public function the_member_reports_drop_the_unit_column_for_a_flat_division(): void
    {
        $division = $this->flatDivision();
        $co       = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::COMMANDING_OFFICER], ['role' => Role::SENIOR_LEADER]);

        $this->actingAs($co);

        $this->get(route('division.inactive-members', $division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('division.hasUnits', false));

        $this->get(route('division.voice-report', $division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('division.hasUnits', false));
    }

    private function flatDivision(): Division
    {
        $division = $this->createActiveDivision();
        DivisionUnitLevel::where('division_id', $division->id)->delete();

        return $division->fresh();
    }

    private function divisionWithCommander(): array
    {
        $division = $this->createActiveDivision();
        $co       = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::COMMANDING_OFFICER], ['role' => Role::SENIOR_LEADER]);

        return [$division, $co];
    }
}
