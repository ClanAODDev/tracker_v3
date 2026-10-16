<?php

namespace Tests\Feature\Services\Units;

use App\Enums\Position;
use App\Enums\Role;
use App\Filament\Mod\Resources\UnitResource\Pages\EditUnit;
use App\Models\Unit;
use App\Models\User;
use App\Services\Units\UnitAssignment;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RemovingTheSquadLevelTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private $division;

    private Unit $platoon;

    private Unit $squad;

    private UnitAssignment $units;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = $this->createActiveDivision();
        $this->platoon  = $this->createPlatoon($this->division);
        $this->squad    = $this->createSquad($this->platoon);
        $this->units    = app(UnitAssignment::class);
    }

    #[Test]
    public function squad_leaders_are_listed_but_platoon_leaders_of_surviving_units_are_not(): void
    {
        $platoonLeader = $this->createPlatoonLeader($this->platoon, ['division_id' => $this->division->id]);
        $squadLeader   = $this->createSquadLeader($this->squad, ['division_id' => $this->division->id]);

        $this->assertSame(
            [$squadLeader->id],
            $this->units->leadersLosingPositions($this->squad)->pluck('id')->all(),
        );

        $this->assertEqualsCanonicalizing(
            [$platoonLeader->id, $squadLeader->id],
            $this->units->leadersLosingPositions($this->platoon)->pluck('id')->all(),
        );
    }

    #[Test]
    public function a_leader_who_also_leads_a_surviving_unit_is_not_listed(): void
    {
        $otherSquad = $this->createSquad($this->platoon);
        $leader     = $this->createSquadLeader($this->squad, ['division_id' => $this->division->id]);
        $otherSquad->update(['leader_id' => $leader->clan_id]);

        $this->assertTrue($this->units->leadersLosingPositions($this->squad)->isEmpty());
    }

    #[Test]
    public function archiving_the_squads_leaves_their_leaders_as_officers_with_no_position(): void
    {
        $squadLeader = $this->createSquadLeader($this->squad, ['division_id' => $this->division->id]);
        $user        = User::factory()->create([
            'member_id' => $squadLeader->id,
            'name'      => $squadLeader->name,
            'role'      => Role::OFFICER,
        ]);

        $this->units->releaseMembers($this->squad);
        $this->units->archive($this->squad);

        $squadLeader->refresh();

        $this->assertSame(Position::MEMBER, $squadLeader->position);
        $this->assertSame($this->platoon->id, $squadLeader->unit_id);
        $this->assertSame(Role::OFFICER, $user->fresh()->role);
    }

    #[Test]
    public function the_delete_warning_names_the_leaders_who_will_lose_their_position(): void
    {
        Filament::setCurrentPanel('mod');
        $admin       = $this->createAdmin();
        $squadLeader = $this->createSquadLeader($this->squad, ['division_id' => $this->division->id, 'name' => 'Jane Leader']);

        $this->actingAs($admin);

        $page        = Livewire::test(EditUnit::class, ['record' => $this->squad->getRouteKey()])->mountAction('delete');
        $description = (string) $page->instance()->getMountedAction()->getModalDescription();

        $this->assertStringContainsString('These leaders will lose their position', $description);
        $this->assertStringContainsString($squadLeader->name, $description);
    }
}
