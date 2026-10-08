<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use App\Models\DivisionUnitLevel;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionStructureTest extends TestCase
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
    public function division_settings_show_each_levels_leader_powers(): void
    {
        [$division, $co] = $this->divisionWithCommander();

        $this->actingAs($co);

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->assertSee('What leaders can do')
            ->assertSee('Approve promotions in their platoon and every squad under it up to Private First Class')
            ->assertSee('Request promotions for members of their squad ranked below Specialist');
    }

    #[Test]
    public function renaming_levels_updates_unit_names_everywhere(): void
    {
        [$division, $co] = $this->divisionWithCommander();
        $this->actingAs($co);

        $page            = Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()]);
        $state           = $page->get('data.unitLevels');
        $keys            = array_keys($state);
        $state[$keys[0]] = ['label' => 'Company', 'label_plural' => 'Companies', 'leader_title' => 'Company Commander'];

        $page->set('data.unitLevels', $state)
            ->assertSee('Approve promotions in their company and every squad under it')
            ->call('save');

        $this->assertSame('Company', DivisionUnitLevel::where('division_id', $division->id)->where('depth', 1)->value('label'));
        $this->assertSame('Company', $division->fresh()->locality('platoon'));
        $this->assertSame('Company Commander', $division->fresh()->locality('platoon leader'));
        $this->assertSame('Squad', $division->fresh()->locality('Squad'));
    }

    #[Test]
    public function a_level_with_units_cannot_be_removed(): void
    {
        [$division, $co] = $this->divisionWithCommander();
        $this->createSquad($this->createPlatoon($division));
        $this->actingAs($co);

        $page  = Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()]);
        $state = $page->get('data.unitLevels');
        array_pop($state);

        $page->set('data.unitLevels', $state)
            ->call('save')
            ->assertHasFormErrors(['unitLevels']);

        $this->assertSame(2, DivisionUnitLevel::where('division_id', $division->id)->count());
    }

    #[Test]
    public function a_division_can_grow_to_four_levels(): void
    {
        [$division, $co] = $this->divisionWithCommander();
        $this->actingAs($co);

        $page  = Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()]);
        $state = $page->get('data.unitLevels');

        $state['new-1'] = ['label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader'];
        $state['new-2'] = ['label' => 'Fireteam', 'label_plural' => 'Fireteams', 'leader_title' => 'Fireteam Leader'];

        $page->set('data.unitLevels', $state)->call('save');

        $this->assertSame(
            ['Platoon', 'Squad', 'Team', 'Fireteam'],
            DivisionUnitLevel::where('division_id', $division->id)->orderBy('depth')->pluck('label')->all()
        );
    }

    private function divisionWithCommander(): array
    {
        $division = $this->createActiveDivision();
        $co       = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::COMMANDING_OFFICER], ['role' => Role::SENIOR_LEADER]);

        return [$division, $co];
    }
}
