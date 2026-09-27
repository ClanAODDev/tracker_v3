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
