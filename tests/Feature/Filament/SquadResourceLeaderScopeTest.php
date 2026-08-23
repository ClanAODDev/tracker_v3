<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Filament\Mod\Resources\SquadResource\Pages\EditSquad;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class SquadResourceLeaderScopeTest extends TestCase
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
    public function leader_id_must_belong_to_the_squads_division(): void
    {
        $division        = $this->createActiveDivision();
        $otherDivision   = $this->createActiveDivision();
        $platoon         = $this->createPlatoon($division);
        $squad           = $this->createSquad($platoon);
        $outsider        = $this->createMember(['division_id' => $otherDivision->id]);
        $originalSquadId = $outsider->squad_id;

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditSquad::class, ['record' => $squad->getRouteKey()])
            ->fillForm(['leader_id' => $outsider->clan_id])
            ->call('save')
            ->assertHasFormErrors(['leader_id']);

        $this->assertNotEquals(Position::SQUAD_LEADER, $outsider->fresh()->position);
        $this->assertEquals($originalSquadId, $outsider->fresh()->squad_id);
    }

    #[Test]
    public function leader_id_can_be_set_to_a_member_of_the_same_division(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditSquad::class, ['record' => $squad->getRouteKey()])
            ->fillForm(['leader_id' => $member->clan_id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(Position::SQUAD_LEADER, $member->fresh()->position);
        $this->assertEquals($squad->id, $member->fresh()->squad_id);
    }
}
