<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Filament\Mod\Resources\PlatoonResource\Pages\EditPlatoon;
use App\Filament\Mod\Resources\SquadResource\Pages\EditSquad;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class LeadershipPositionExclusivityTest extends TestCase
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
    public function removing_a_platoon_leader_who_is_now_an_xo_keeps_them_xo(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $xo       = $this->createExecutiveOfficer($division, ['platoon_id' => 0, 'squad_id' => 0]);
        $platoon->update(['leader_id' => $xo->clan_id]);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditPlatoon::class, ['record' => $platoon->getRouteKey()])
            ->fillForm(['leader_id' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(Position::EXECUTIVE_OFFICER, $xo->fresh()->position);
        $this->assertNull($platoon->fresh()->leader_id);
    }

    #[Test]
    public function removing_a_platoon_leader_demotes_them_to_member(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $leader   = $this->createPlatoonLeader($platoon);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditPlatoon::class, ['record' => $platoon->getRouteKey()])
            ->fillForm(['leader_id' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(Position::MEMBER, $leader->fresh()->position);
    }

    #[Test]
    public function an_xo_cannot_be_assigned_as_platoon_leader(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $xo       = $this->createExecutiveOfficer($division);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditPlatoon::class, ['record' => $platoon->getRouteKey()])
            ->fillForm(['leader_id' => $xo->clan_id])
            ->call('save')
            ->assertHasFormErrors(['leader_id']);

        $this->assertEquals(Position::EXECUTIVE_OFFICER, $xo->fresh()->position);
        $this->assertNull($platoon->fresh()->leader_id);
    }

    #[Test]
    public function a_squad_leader_cannot_be_assigned_as_platoon_leader(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $leader   = $this->createSquadLeader($squad);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditPlatoon::class, ['record' => $platoon->getRouteKey()])
            ->fillForm(['leader_id' => $leader->clan_id])
            ->call('save')
            ->assertHasFormErrors(['leader_id']);

        $this->assertEquals(Position::SQUAD_LEADER, $leader->fresh()->position);
        $this->assertEquals($leader->clan_id, $squad->fresh()->leader_id);
    }

    #[Test]
    public function saving_a_platoon_with_its_current_leader_is_allowed(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $leader   = $this->createPlatoonLeader($platoon);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditPlatoon::class, ['record' => $platoon->getRouteKey()])
            ->fillForm(['leader_id' => $leader->clan_id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(Position::PLATOON_LEADER, $leader->fresh()->position);
    }

    #[Test]
    public function a_platoon_leader_cannot_be_assigned_as_squad_leader(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $leader   = $this->createPlatoonLeader($platoon);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditSquad::class, ['record' => $squad->getRouteKey()])
            ->fillForm(['leader_id' => $leader->clan_id])
            ->call('save')
            ->assertHasFormErrors(['leader_id']);

        $this->assertEquals(Position::PLATOON_LEADER, $leader->fresh()->position);
        $this->assertEquals($leader->clan_id, $platoon->fresh()->leader_id);
    }

    #[Test]
    public function a_commanding_officer_cannot_be_assigned_as_squad_leader(): void
    {
        $division = $this->createActiveDivision();
        $squad    = $this->createSquad($this->createPlatoon($division));
        $co       = $this->createCommander($division);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditSquad::class, ['record' => $squad->getRouteKey()])
            ->fillForm(['leader_id' => $co->clan_id])
            ->call('save')
            ->assertHasFormErrors(['leader_id']);

        $this->assertEquals(Position::COMMANDING_OFFICER, $co->fresh()->position);
    }

    #[Test]
    public function removing_a_squad_leader_who_is_now_an_xo_keeps_them_xo(): void
    {
        $division = $this->createActiveDivision();
        $squad    = $this->createSquad($this->createPlatoon($division));
        $xo       = $this->createExecutiveOfficer($division, ['platoon_id' => 0, 'squad_id' => 0]);
        $squad->update(['leader_id' => $xo->clan_id]);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditSquad::class, ['record' => $squad->getRouteKey()])
            ->fillForm(['leader_id' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(Position::EXECUTIVE_OFFICER, $xo->fresh()->position);
        $this->assertNull($squad->fresh()->leader_id);
    }
}
