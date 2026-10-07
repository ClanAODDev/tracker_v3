<?php

namespace Tests\Feature\Services\Units;

use App\Models\Platoon;
use App\Models\Squad;
use App\Services\Units\UnitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitAssignmentTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private UnitAssignment $units;

    protected function setUp(): void
    {
        parent::setUp();

        $this->units = $this->app->make(UnitAssignment::class);
        $this->actingAs($this->createAdmin());
    }

    #[Test]
    public function creating_units_writes_the_platoon_and_squad_rows_too(): void
    {
        $division = $this->createActiveDivision();

        $platoonUnit = $this->units->create($division, null, ['name' => 'Alpha', 'description' => 'First', 'order' => 3]);
        $squadUnit   = $this->units->create($division, $platoonUnit, ['name' => 'Alpha One', 'gen_pop' => true]);

        $platoon = Platoon::findOrFail($platoonUnit->legacy_id);
        $squad   = Squad::findOrFail($squadUnit->legacy_id);

        $this->assertSame(['Alpha', 'First', 3, $division->id], [$platoon->name, $platoon->description, $platoon->order, $platoon->division_id]);
        $this->assertSame(['Alpha One', $platoon->id, true], [$squad->name, $squad->platoon_id, (bool) $squad->gen_pop]);
        $this->assertSame("/{$platoonUnit->id}/{$squadUnit->id}/", $squadUnit->fresh()->path);
        $this->assertSame(2, DB::table('activities')->whereIn('subject_type', [Platoon::class, Squad::class])->count());
        $this->artisan('tracker:units-verify')->assertSuccessful();
    }

    #[Test]
    public function units_cannot_be_nested_deeper_than_squads_while_writing_back(): void
    {
        $division = $this->createActiveDivision();
        $squad    = $this->units->create($division, $this->units->create($division, null, ['name' => 'P']), ['name' => 'S']);

        $this->expectException(InvalidArgumentException::class);

        $this->units->create($division, $squad, ['name' => 'Too deep']);
    }

    #[Test]
    public function member_columns_resolve_to_the_matching_platoon_and_squad(): void
    {
        $division    = $this->createActiveDivision();
        $platoonUnit = $this->units->create($division, null, ['name' => 'P']);
        $squadUnit   = $this->units->create($division, $platoonUnit, ['name' => 'S']);

        $this->assertSame(['unit_id' => $squadUnit->id, 'platoon_id' => $platoonUnit->legacy_id, 'squad_id' => $squadUnit->legacy_id], $this->units->columnsFor($squadUnit));
        $this->assertSame(['unit_id' => $platoonUnit->id, 'platoon_id' => $platoonUnit->legacy_id, 'squad_id' => 0], $this->units->columnsFor($platoonUnit));
        $this->assertSame(['unit_id' => null, 'platoon_id' => 0, 'squad_id' => 0], $this->units->columnsFor(null));

        $member = $this->createMember(['division_id' => $division->id, ...$this->units->columnsFor($squadUnit)]);
        $this->assertSame($squadUnit->legacy_id, $member->fresh()->squad_id);
        $this->artisan('tracker:units-verify')->assertSuccessful();
    }

    #[Test]
    public function updating_archiving_and_restoring_write_back(): void
    {
        $division = $this->createActiveDivision();
        $unit     = $this->units->create($division, null, ['name' => 'Old']);

        $this->units->update($unit, ['name' => 'New', 'gen_pop' => true]);
        $this->assertSame('New', Platoon::find($unit->legacy_id)->name);

        $this->units->archive($unit);
        $this->assertSoftDeleted('platoons', ['id' => $unit->legacy_id]);
        $this->assertSoftDeleted('units', ['id' => $unit->id]);

        $this->units->restore($unit);
        $this->assertNotSoftDeleted('platoons', ['id' => $unit->legacy_id]);
        $this->artisan('tracker:units-verify')->assertSuccessful();
    }

    #[Test]
    public function leaders_are_set_and_cleared_on_both_sides(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->units->create($division, null, ['name' => 'P']);
        $squad    = $this->units->create($division, $platoon, ['name' => 'S']);
        $leader   = $this->createMember(['division_id' => $division->id]);

        $this->units->setLeader($platoon, $leader->clan_id);
        $this->units->setLeader($squad, $leader->clan_id);
        $this->assertSame($leader->clan_id, Platoon::find($platoon->legacy_id)->leader_id);

        $this->assertSame(1, $this->units->clearLeadership([$leader->clan_id], except: $platoon));
        $this->assertNull(Squad::find($squad->legacy_id)->leader_id);
        $this->assertSame($leader->clan_id, Platoon::find($platoon->legacy_id)->leader_id);
        $this->assertSame($leader->clan_id, $platoon->fresh()->leader_id);
        $this->artisan('tracker:units-verify')->assertSuccessful();
    }

    #[Test]
    public function units_for_legacy_rows_are_found_including_archived(): void
    {
        $division = $this->createActiveDivision();
        $unit     = $this->units->create($division, null, ['name' => 'P']);
        $this->units->archive($unit);

        $this->assertSame($unit->id, $this->units->forLegacy(Platoon::withTrashed()->find($unit->legacy_id))->id);
    }
}
