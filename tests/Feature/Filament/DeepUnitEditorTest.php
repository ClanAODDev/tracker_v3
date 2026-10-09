<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\UnitResource\Pages\EditUnit;
use App\Filament\Mod\Resources\UnitResource\Pages\ListUnits;
use App\Filament\Mod\Resources\UnitResource\RelationManagers\ChildrenRelationManager;
use App\Models\Division;
use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DeepUnitEditorTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private Division $division;

    private Unit $company;

    private Unit $platoon;

    private Unit $squad;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('mod');

        $this->division = $this->createActiveDivision();
        DivisionUnitLevel::where('division_id', $this->division->id)->where('depth', 1)->update(['label' => 'Company', 'label_plural' => 'Companies']);
        DivisionUnitLevel::where('division_id', $this->division->id)->where('depth', 2)->update(['label' => 'Platoon', 'label_plural' => 'Platoons']);
        DivisionUnitLevel::create(['division_id' => $this->division->id, 'depth' => 3, 'label' => 'Squad', 'label_plural' => 'Squads', 'leader_title' => 'Squad Leader']);

        $this->company = $this->createPlatoon($this->division->fresh());
        $this->platoon = Unit::factory()->childOf($this->company)->create();
        $this->squad   = Unit::factory()->childOf($this->platoon)->create();

        $this->actingAs($this->createSeniorLeader($this->division));
    }

    #[Test]
    public function a_middle_level_unit_manages_its_own_children(): void
    {
        Livewire::test(ChildrenRelationManager::class, ['ownerRecord' => $this->platoon->fresh(), 'pageClass' => EditUnit::class])
            ->assertSee($this->squad->name)
            ->assertDontSee('Platoons');

        $this->assertSame('Squads', ChildrenRelationManager::getTitle($this->platoon->fresh(), EditUnit::class));
        $this->assertSame('Platoons', ChildrenRelationManager::getTitle($this->company->fresh(), EditUnit::class));
    }

    #[Test]
    public function the_lowest_level_has_no_children_to_manage(): void
    {
        $this->assertFalse(ChildrenRelationManager::canViewForRecord($this->squad->fresh(), EditUnit::class));
        $this->assertTrue(ChildrenRelationManager::canViewForRecord($this->platoon->fresh(), EditUnit::class));
    }

    #[Test]
    public function every_level_uses_the_one_unit_editor(): void
    {
        Livewire::test(EditUnit::class, ['record' => $this->platoon->getRouteKey()])->assertOk();
        Livewire::test(EditUnit::class, ['record' => $this->squad->getRouteKey()])->assertOk();
    }

    #[Test]
    public function units_cannot_be_created_below_the_lowest_level(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        app(UnitAssignment::class)->create($this->division->fresh(), $this->squad->fresh(), ['name' => 'Too deep']);
    }

    #[Test]
    public function deleting_a_unit_archives_everything_beneath_it(): void
    {
        Livewire::test(EditUnit::class, ['record' => $this->company->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($this->company);
        $this->assertSoftDeleted($this->platoon);
        $this->assertSoftDeleted($this->squad);
    }

    #[Test]
    public function deleting_the_lowest_level_moves_its_members_up_to_the_parent(): void
    {
        $member = $this->createMember(['division_id' => $this->division->id, 'unit_id' => $this->squad->id]);

        Livewire::test(EditUnit::class, ['record' => $this->squad->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($this->squad);
        $this->assertNotSoftDeleted($this->platoon);
        $this->assertSame($this->platoon->id, $member->fresh()->unit_id);
    }

    #[Test]
    public function changing_a_leader_names_the_levels_own_leader_title(): void
    {
        DivisionUnitLevel::where('division_id', $this->division->id)->where('depth', 3)->update(['label' => 'Fireteam', 'leader_title' => 'Fireteam Lead']);
        $member = $this->createMember(['division_id' => $this->division->id, 'unit_id' => $this->squad->id]);

        Livewire::test(EditUnit::class, ['record' => $this->squad->getRouteKey()])
            ->fillForm(['leader_id' => $member->clan_id])
            ->assertSee('will become the Fireteam Lead of this fireteam')
            ->assertDontSee('Squad Leader');
    }

    #[Test]
    public function the_unit_list_shows_every_level_of_the_division(): void
    {
        Livewire::test(ListUnits::class)
            ->assertCanSeeTableRecords([$this->company, $this->platoon, $this->squad]);
    }
}
