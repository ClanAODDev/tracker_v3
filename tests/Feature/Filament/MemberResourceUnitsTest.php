<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Filament\Mod\Resources\MemberResource\Pages\ListMembers;
use App\Models\DivisionUnitLevel;
use App\Models\Unit;
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
        $member   = $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id]);

        $this->actingAs($leader);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->assertFormSet([
                'unit_level_1' => $platoon->id,
                'unit_level_2' => $squad->id,
            ]);
    }

    #[Test]
    public function the_unit_filter_includes_squad_members_for_a_platoon_and_is_exact_for_a_squad(): void
    {
        $leader    = $this->createSeniorLeader();
        $division  = $leader->member->division;
        $platoon   = $this->createPlatoon($division);
        $squad     = $this->createSquad($platoon);
        $inPlatoon = $this->createMember(['division_id' => $division->id, 'unit_id' => $platoon->id]);
        $inSquad   = $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id]);
        $elsewhere = $this->createMember(['division_id' => $division->id, 'unit_id' => $this->createPlatoon($division)->id]);

        $this->actingAs($leader);

        Livewire::test(ListMembers::class)
            ->filterTable('unit', ['division' => $division->id, 'platoon' => [$platoon->id], 'squad' => []])
            ->assertCanSeeTableRecords([$inPlatoon, $inSquad])
            ->assertCanNotSeeTableRecords([$elsewhere]);

        Livewire::test(ListMembers::class)
            ->filterTable('unit', ['division' => $division->id, 'platoon' => [$platoon->id], 'squad' => [$squad->id]])
            ->assertCanSeeTableRecords([$inSquad])
            ->assertCanNotSeeTableRecords([$inPlatoon, $elsewhere]);
    }

    #[Test]
    public function clearing_the_pickers_unassigns_the_member(): void
    {
        $leader   = $this->createSeniorLeader();
        $division = $leader->member->division;
        $platoon  = $this->createPlatoon($division);
        $member   = $this->createMember(['division_id' => $division->id, 'unit_id' => $platoon->id]);

        $this->actingAs($leader);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['unit_level_1' => null, 'unit_level_2' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($member->fresh()->unit_id);
    }

    #[Test]
    public function the_edit_form_assigns_a_member_to_a_unit_three_levels_deep(): void
    {
        $leader   = $this->createSeniorLeader();
        $division = $leader->member->division;
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);
        $company = $this->createPlatoon($division);
        $middle  = Unit::factory()->childOf($company)->create();
        $team    = Unit::factory()->childOf($middle)->create();
        $member  = $this->createMember(['division_id' => $division->id, 'unit_id' => $company->id]);

        $this->actingAs($leader);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['unit_level_1' => $company->id, 'unit_level_2' => $middle->id, 'unit_level_3' => $team->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($team->id, $member->fresh()->unit_id);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->assertFormSet(['unit_level_1' => $company->id, 'unit_level_2' => $middle->id, 'unit_level_3' => $team->id]);
    }

    #[Test]
    public function the_unit_filter_reaches_units_below_the_second_level(): void
    {
        $leader   = $this->createSeniorLeader();
        $division = $leader->member->division;
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);
        $company  = $this->createPlatoon($division);
        $middle   = Unit::factory()->childOf($company)->create();
        $team     = Unit::factory()->childOf($middle)->create();
        $inMiddle = $this->createMember(['division_id' => $division->id, 'unit_id' => $middle->id]);
        $inTeam   = $this->createMember(['division_id' => $division->id, 'unit_id' => $team->id]);

        $this->actingAs($leader);

        Livewire::test(ListMembers::class)
            ->filterTable('unit', ['division' => $division->id, 'platoon' => [$company->id], 'squad' => [$team->id]])
            ->assertCanSeeTableRecords([$inTeam])
            ->assertCanNotSeeTableRecords([$inMiddle]);

        Livewire::test(ListMembers::class)
            ->filterTable('unit', ['division' => $division->id, 'platoon' => [$company->id], 'squad' => [$middle->id]])
            ->assertCanSeeTableRecords([$inTeam, $inMiddle]);
    }
}
