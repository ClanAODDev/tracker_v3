<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Filament\Mod\Resources\PlatoonResource\Pages\EditPlatoon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class PlatoonResourceLeaderScopeTest extends TestCase
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
    public function leader_id_must_belong_to_the_platoons_division(): void
    {
        $division          = $this->createActiveDivision();
        $otherDivision     = $this->createActiveDivision();
        $platoon           = $this->createPlatoon($division);
        $outsider          = $this->createMember(['division_id' => $otherDivision->id]);
        $originalPlatoonId = $outsider->platoon_id;

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditPlatoon::class, ['record' => $platoon->getRouteKey()])
            ->fillForm(['leader_id' => $outsider->clan_id])
            ->call('save')
            ->assertHasFormErrors(['leader_id']);

        $this->assertNotEquals(Position::PLATOON_LEADER, $outsider->fresh()->position);
        $this->assertEquals($originalPlatoonId, $outsider->fresh()->platoon_id);
    }

    #[Test]
    public function leader_id_can_be_set_to_a_member_of_the_same_division(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditPlatoon::class, ['record' => $platoon->getRouteKey()])
            ->fillForm(['leader_id' => $member->clan_id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(Position::PLATOON_LEADER, $member->fresh()->position);
        $this->assertEquals($platoon->id, $member->fresh()->platoon_id);
    }
}
