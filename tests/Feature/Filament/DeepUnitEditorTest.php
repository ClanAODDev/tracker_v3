<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\PlatoonResource\Pages\EditPlatoon;
use App\Filament\Mod\Resources\PlatoonResource\RelationManagers\SquadsRelationManager;
use App\Filament\Mod\Resources\SquadResource\Pages\EditSquad;
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
        Livewire::test(SquadsRelationManager::class, ['ownerRecord' => $this->platoon->fresh(), 'pageClass' => EditPlatoon::class])
            ->assertSee($this->squad->name)
            ->assertDontSee('Platoons');

        $this->assertSame('Squads', SquadsRelationManager::getTitle($this->platoon->fresh(), EditPlatoon::class));
        $this->assertSame('Platoons', SquadsRelationManager::getTitle($this->company->fresh(), EditPlatoon::class));
    }

    #[Test]
    public function the_lowest_level_has_no_children_to_manage(): void
    {
        $this->assertFalse(SquadsRelationManager::canViewForRecord($this->squad->fresh(), EditSquad::class));
        $this->assertTrue(SquadsRelationManager::canViewForRecord($this->platoon->fresh(), EditPlatoon::class));
    }

    #[Test]
    public function middle_levels_use_the_platoon_editor_and_the_lowest_uses_the_squad_editor(): void
    {
        Livewire::test(EditPlatoon::class, ['record' => $this->platoon->getRouteKey()])->assertOk();
        Livewire::test(EditSquad::class, ['record' => $this->squad->getRouteKey()])->assertOk();
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
        Livewire::test(EditPlatoon::class, ['record' => $this->company->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($this->company);
        $this->assertSoftDeleted($this->platoon);
        $this->assertSoftDeleted($this->squad);
    }
}
