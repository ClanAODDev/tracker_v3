<?php

namespace Tests\Feature\Services\Units;

use App\Enums\Position;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision as EditModDivision;
use App\Filament\Mod\Resources\DivisionResource\RelationManagers\PlatoonsRelationManager;
use App\Filament\Mod\Resources\UnitResource\Pages\EditUnit;
use App\Filament\Mod\Resources\UnitResource\RelationManagers\ChildrenRelationManager;
use App\Jobs\CleanupUnassignedLeaders;
use App\Models\Unit;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitLeadershipWritersTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private $division;

    private Unit $platoon;

    private Unit $squad;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('mod');
        $this->division = $this->createActiveDivision();
        $this->platoon  = $this->createPlatoon($this->division);
        $this->squad    = $this->createSquad($this->platoon);
        $this->actingAs($this->createAdmin());
    }

    #[Test]
    public function changing_a_platoon_leader_moves_leadership_on_units(): void
    {
        $old = $this->createPlatoonLeader($this->platoon, ['division_id' => $this->division->id]);
        $new = $this->createMember(['division_id' => $this->division->id]);

        Livewire::test(EditUnit::class, ['record' => $this->platoon->getRouteKey()])
            ->fillForm(['leader_id' => $new->clan_id, 'name' => 'Renamed'])
            ->call('save')
            ->assertHasNoFormErrors();

        $unit = $this->platoon->fresh();
        $this->assertSame([$new->clan_id, 'Renamed'], [$unit->leader_id, $unit->name]);
        $this->assertSame([Position::PLATOON_LEADER, $unit->id], [$new->fresh()->position, $new->fresh()->unit_id]);
        $this->assertSame([Position::MEMBER, null], [$old->fresh()->position, $old->fresh()->unit_id]);
    }

    #[Test]
    public function changing_a_squad_leader_moves_leadership_on_units(): void
    {
        $old = $this->createSquadLeader($this->squad, ['division_id' => $this->division->id]);
        $new = $this->createMember(['division_id' => $this->division->id]);

        Livewire::test(EditUnit::class, ['record' => $this->squad->getRouteKey()])
            ->fillForm(['leader_id' => $new->clan_id])
            ->call('save')
            ->assertHasNoFormErrors();

        $unit = $this->squad->fresh();
        $this->assertSame($new->clan_id, $unit->leader_id);
        $this->assertSame($unit->id, $new->fresh()->unit_id);
        $this->assertSame([Position::MEMBER, null], [$old->fresh()->position, $old->fresh()->unit_id]);
    }

    #[Test]
    public function deleting_a_squad_moves_its_members_up_to_the_platoon(): void
    {
        $member = $this->createMember(['division_id' => $this->division->id, 'unit_id' => $this->squad->id]);

        Livewire::test(EditUnit::class, ['record' => $this->squad->getRouteKey()])->callAction('delete');

        $this->assertSoftDeleted('units', ['id' => $this->squad->fresh()->id]);
        $this->assertSame($this->platoon->fresh()->id, $member->fresh()->unit_id);
    }

    #[Test]
    public function deleting_a_platoon_archives_its_squads_and_unassigns_members(): void
    {
        $member = $this->createMember(['division_id' => $this->division->id, 'unit_id' => $this->squad->id]);

        Livewire::test(EditUnit::class, ['record' => $this->platoon->getRouteKey()])->callAction('delete');

        $this->assertSoftDeleted('units', ['id' => $this->platoon->fresh()->id]);
        $this->assertSoftDeleted('units', ['id' => $this->squad->fresh()->id]);
        $this->assertNull($member->fresh()->unit_id);
    }

    #[Test]
    public function creating_platoons_and_squads_creates_units(): void
    {
        Livewire::test(PlatoonsRelationManager::class, ['ownerRecord' => $this->division, 'pageClass' => EditModDivision::class])
            ->callAction(TestAction::make('create')->table(), ['name' => 'Bravo']);

        $platoon = Unit::where('name', 'Bravo')->sole();
        $this->assertNull($platoon->parent_id);

        Livewire::test(ChildrenRelationManager::class, ['ownerRecord' => $platoon, 'pageClass' => EditUnit::class])
            ->callAction(TestAction::make('create')->table(), ['name' => 'Bravo One']);

        $squad = Unit::where('name', 'Bravo One')->sole();
        $this->assertSame($platoon->id, $squad->parent_id);
    }

    #[Test]
    public function the_weekly_cleanup_clears_stale_leaders_on_units(): void
    {
        $leader = $this->createPlatoonLeader($this->platoon, ['division_id' => $this->division->id]);
        $leader->update(['unit_id' => null, 'division_id' => 0, 'position' => Position::MEMBER]);

        (new CleanupUnassignedLeaders)->handle();

        $this->assertNull($this->platoon->fresh()->leader_id);
    }
}
