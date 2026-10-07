<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Filament\Mod\Resources\MemberResource\Pages\ListMembers;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberResourceUnitsTest extends TestCase
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
    public function the_edit_form_shows_the_members_platoon_and_squad_units(): void
    {
        $leader   = $this->createSeniorLeader();
        $division = $leader->member->division;
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $member   = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id, 'squad_id' => $squad->id]);

        $this->actingAs($leader);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->assertFormSet([
                'platoon_unit_id' => $this->unitFor($platoon)->id,
                'squad_unit_id'   => $this->unitFor($squad)->id,
            ]);
    }

    #[Test]
    public function the_unit_filter_includes_squad_members_for_a_platoon_and_is_exact_for_a_squad(): void
    {
        $leader    = $this->createSeniorLeader();
        $division  = $leader->member->division;
        $platoon   = $this->createPlatoon($division);
        $squad     = $this->createSquad($platoon);
        $inPlatoon = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id]);
        $inSquad   = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id, 'squad_id' => $squad->id]);
        $elsewhere = $this->createMember(['division_id' => $division->id, 'platoon_id' => $this->createPlatoon($division)->id]);

        $this->actingAs($leader);

        Livewire::test(ListMembers::class)
            ->filterTable('unit', ['division' => $division->id, 'platoon' => [$this->unitFor($platoon)->id], 'squad' => []])
            ->assertCanSeeTableRecords([$inPlatoon, $inSquad])
            ->assertCanNotSeeTableRecords([$elsewhere]);

        Livewire::test(ListMembers::class)
            ->filterTable('unit', ['division' => $division->id, 'platoon' => [$this->unitFor($platoon)->id], 'squad' => [$this->unitFor($squad)->id]])
            ->assertCanSeeTableRecords([$inSquad])
            ->assertCanNotSeeTableRecords([$inPlatoon, $elsewhere]);
    }

    #[Test]
    public function clearing_the_pickers_unassigns_the_member(): void
    {
        $leader   = $this->createSeniorLeader();
        $division = $leader->member->division;
        $platoon  = $this->createPlatoon($division);
        $member   = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id]);

        $this->actingAs($leader);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['platoon_unit_id' => null, 'squad_unit_id' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([null, 0, 0], [$member->fresh()->unit_id, $member->fresh()->platoon_id, $member->fresh()->squad_id]);
    }
}
