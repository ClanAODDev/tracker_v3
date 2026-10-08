<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use App\Filament\Mod\Resources\UnitResource\Pages\EditUnit;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitRestructureTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private const COMPANY = ['label' => 'Company', 'label_plural' => 'Companies', 'leader_title' => 'Company Commander'];

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('mod');
    }

    #[Test]
    public function a_new_top_level_adopts_every_existing_unit(): void
    {
        $division = $this->createActiveDivision();
        $platoonA = $this->createPlatoon($division);
        $platoonB = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoonA);
        $member   = $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id]);

        $company = app(UnitAssignment::class)->insertTopLevel($division, self::COMPANY, ['name' => 'Alpha']);

        $this->assertSame(1, $company->depth);
        $this->assertNull($company->parent_id);
        $this->assertSame("/{$company->id}/", $company->path);

        $this->assertSame(2, $platoonA->fresh()->depth);
        $this->assertSame($company->id, $platoonA->fresh()->parent_id);
        $this->assertSame($company->id, $platoonB->fresh()->parent_id);
        $this->assertSame("/{$company->id}/{$platoonA->id}/", $platoonA->fresh()->path);

        $this->assertSame(3, $squad->fresh()->depth);
        $this->assertSame($platoonA->id, $squad->fresh()->parent_id);
        $this->assertSame("/{$company->id}/{$platoonA->id}/{$squad->id}/", $squad->fresh()->path);
        $this->assertSame($squad->id, $member->fresh()->unit_id);
    }

    #[Test]
    public function the_levels_shift_down_and_the_new_one_sits_on_top(): void
    {
        $division = $this->createActiveDivision();
        $this->createPlatoon($division);

        app(UnitAssignment::class)->insertTopLevel($division, self::COMPANY, ['name' => 'Alpha']);

        $this->assertSame(
            ['Company', 'Platoon', 'Squad'],
            $division->unitLevels()->orderBy('depth')->pluck('label')->all()
        );
        $this->assertSame(3, $division->fresh()->deepestUnitLevel());
    }

    #[Test]
    public function archived_units_are_carried_along_so_they_restore_cleanly(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        app(UnitAssignment::class)->archive($squad);

        $company = app(UnitAssignment::class)->insertTopLevel($division, self::COMPANY, ['name' => 'Alpha']);

        $archived = Unit::withTrashed()->find($squad->id);
        $this->assertSame(3, $archived->depth);
        $this->assertSame("/{$company->id}/{$platoon->id}/{$squad->id}/", $archived->path);
    }

    #[Test]
    public function a_level_cannot_be_added_beyond_four_or_to_a_flat_division(): void
    {
        $division = $this->createActiveDivision();
        $units    = app(UnitAssignment::class);

        $units->insertTopLevel($division, self::COMPANY, ['name' => 'Alpha']);
        $units->insertTopLevel($division->fresh(), ['label' => 'Battalion', 'label_plural' => 'Battalions', 'leader_title' => 'Battalion Commander'], ['name' => 'First']);

        try {
            $units->insertTopLevel($division->fresh(), self::COMPANY, ['name' => 'Too many']);
            $this->fail('A fifth level was added.');
        } catch (InvalidArgumentException) {
            $this->assertSame(4, $division->unitLevels()->count());
        }

        $flat = $this->createActiveDivision();
        $flat->unitLevels()->delete();

        $this->expectException(InvalidArgumentException::class);
        $units->insertTopLevel($flat->fresh(), self::COMPANY, ['name' => 'Alpha']);
    }

    #[Test]
    public function a_unit_moves_with_everything_beneath_it(): void
    {
        $division = $this->createActiveDivision();
        $units    = app(UnitAssignment::class);
        $company1 = $units->insertTopLevel($division, self::COMPANY, ['name' => 'Alpha']);
        $platoon  = $this->createChild($company1);
        $squad    = $this->createChild($platoon);
        $company2 = Unit::factory()->create(['division_id' => $division->id, 'depth' => 1]);
        $other    = $this->createChild($company2);

        $units->move($platoon->fresh(), $company2->fresh());

        $this->assertSame($company2->id, $platoon->fresh()->parent_id);
        $this->assertSame("/{$company2->id}/{$platoon->id}/", $platoon->fresh()->path);
        $this->assertSame("/{$company2->id}/{$platoon->id}/{$squad->id}/", $squad->fresh()->path);
        $this->assertSame("/{$company2->id}/{$other->id}/", $other->fresh()->path);
        $this->assertSame([$platoon->id, $squad->id], $company2->fresh()->descendantsQuery()->whereKeyNot($other->id)->orderBy('depth')->pluck('id')->all());
    }

    #[Test]
    public function a_unit_can_only_move_under_a_unit_one_level_above(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $foreign  = $this->createPlatoon($this->createActiveDivision());

        $units = app(UnitAssignment::class);

        foreach ([[$platoon, null], [$squad, $squad], [$squad, $foreign], [$platoon, $squad]] as [$unit, $target]) {
            try {
                $units->move($unit->fresh(), $target?->fresh());
                $this->fail('An invalid move was allowed.');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    #[Test]
    public function the_move_action_reparents_a_unit(): void
    {
        $division = $this->createActiveDivision();
        $platoonA = $this->createPlatoon($division);
        $platoonB = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoonA);
        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditUnit::class, ['record' => $squad->getRouteKey()])
            ->callAction('move', data: ['parent_id' => $platoonB->id]);

        $this->assertSame($platoonB->id, $squad->fresh()->parent_id);
    }

    #[Test]
    public function a_top_level_unit_has_no_move_action(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $this->actingAs($this->createSeniorLeader($division));

        Livewire::test(EditUnit::class, ['record' => $platoon->getRouteKey()])
            ->assertActionHidden('move');
    }

    #[Test]
    public function a_platoon_leader_cannot_move_units(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $leader   = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::PLATOON_LEADER, 'unit_id' => $platoon->id], ['role' => Role::OFFICER]);
        $platoon->update(['leader_id' => $leader->member->clan_id]);
        $this->actingAs($leader);

        Livewire::test(EditUnit::class, ['record' => $squad->getRouteKey()])
            ->assertActionHidden('move');
    }

    #[Test]
    public function the_structure_settings_add_a_level_above_the_existing_ones(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $co       = $this->createMemberWithUser(['division_id' => $division->id, 'position' => Position::COMMANDING_OFFICER], ['role' => Role::SENIOR_LEADER]);
        $this->actingAs($co);

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->callAction(TestAction::make('add_top_level')->schemaComponent('structure_actions'), data: [...self::COMPANY, 'unit_name' => 'Alpha']);

        $this->assertSame(3, $division->fresh()->deepestUnitLevel());
        $this->assertSame('Alpha', $platoon->fresh()->parent->name);
    }

    private function createChild(Unit $parent): Unit
    {
        return Unit::factory()->childOf($parent)->create();
    }
}
