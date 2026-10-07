<?php

namespace Tests\Feature\Console;

use App\Models\DivisionUnitLevel;
use App\Models\Member;
use App\Models\Squad;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitsSyncTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_builds_the_unit_tree_and_assigns_members(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division, ['name' => 'Alpha']);
        $squad    = $this->createSquad($platoon, ['name' => 'Alpha One']);
        $pl       = $this->createPlatoonLeader($platoon);
        $sl       = $this->createSquadLeader($squad);
        $member   = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id, 'squad_id' => $squad->id]);
        $loose    = $this->createMember(['division_id' => $division->id]);

        $this->artisan('tracker:units-sync')->assertSuccessful();

        $platoonUnit = $this->unitFor(Unit::LEGACY_PLATOON, $platoon->id);
        $squadUnit   = $this->unitFor(Unit::LEGACY_SQUAD, $squad->id);

        $this->assertSame([1, null, $division->id, 'Alpha', $pl->clan_id, "/{$platoonUnit->id}/"], [$platoonUnit->depth, $platoonUnit->parent_id, $platoonUnit->division_id, $platoonUnit->name, $platoonUnit->leader_id, $platoonUnit->path]);
        $this->assertSame([2, $platoonUnit->id, $division->id, 'Alpha One', $sl->clan_id, "/{$platoonUnit->id}/{$squadUnit->id}/"], [$squadUnit->depth, $squadUnit->parent_id, $squadUnit->division_id, $squadUnit->name, $squadUnit->leader_id, $squadUnit->path]);
        $this->assertSame($squadUnit->id, $member->fresh()->unit_id);
        $this->assertSame($squadUnit->id, $sl->fresh()->unit_id);
        $this->assertSame($platoonUnit->id, $pl->fresh()->unit_id);
        $this->assertNull($loose->fresh()->unit_id);

        $this->artisan('tracker:units-verify')->assertSuccessful();
    }

    #[Test]
    public function archived_and_orphaned_units_migrate_as_archived(): void
    {
        $division        = $this->createActiveDivision();
        $archivedPlatoon = $this->createPlatoon($division);
        $squadUnderIt    = $this->createSquad($archivedPlatoon);
        $archivedPlatoon->delete();
        $orphan        = Squad::factory()->create(['platoon_id' => 999999]);
        $archivedSquad = $this->createSquad($this->createPlatoon($division));
        $archivedSquad->delete();

        $this->artisan('tracker:units-sync')->assertSuccessful();

        $this->assertNotNull(Unit::withTrashed()->where('legacy_type', Unit::LEGACY_PLATOON)->where('legacy_id', $archivedPlatoon->id)->sole()->deleted_at);
        $this->assertNotNull(Unit::withTrashed()->where('legacy_type', Unit::LEGACY_SQUAD)->where('legacy_id', $squadUnderIt->id)->sole()->deleted_at);
        $this->assertNotNull(Unit::withTrashed()->where('legacy_type', Unit::LEGACY_SQUAD)->where('legacy_id', $archivedSquad->id)->sole()->deleted_at);

        $orphanUnit = Unit::withTrashed()->where('legacy_type', Unit::LEGACY_SQUAD)->where('legacy_id', $orphan->id)->sole();
        $this->assertNotNull($orphanUnit->deleted_at);
        $this->assertNull($orphanUnit->division_id);
        $this->assertNull($orphanUnit->parent_id);

        $this->artisan('tracker:units-verify')->assertSuccessful();
    }

    #[Test]
    public function inconsistent_assignments_get_no_unit_and_are_reported_without_failing(): void
    {
        $divisionA = $this->createActiveDivision();
        $divisionB = $this->createActiveDivision();
        $platoon   = $this->createPlatoon($divisionA);
        $member    = $this->createMember(['division_id' => $divisionB->id, 'platoon_id' => $platoon->id]);

        Artisan::call('tracker:units-sync');
        $exit   = Artisan::call('tracker:units-verify', ['--json' => true]);
        $report = collect(json_decode(Artisan::output(), true))->keyBy('key');

        $this->assertNull($member->fresh()->unit_id);
        $this->assertSame(0, $exit);
        $this->assertSame([$member->id], $report['inconsistent_members_without_unit']['ids']);
        $this->assertSame([], $report['consistent_members_without_unit']['ids']);
    }

    #[Test]
    public function re_running_updates_in_place_and_removes_units_whose_source_is_gone(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division, ['name' => 'Old name']);
        $doomed   = $this->createSquad($platoon);

        $this->artisan('tracker:units-sync')->assertSuccessful();
        $unitId = $this->unitFor(Unit::LEGACY_PLATOON, $platoon->id)->id;

        $platoon->update(['name' => 'New name']);
        DB::table('squads')->where('id', $doomed->id)->delete();

        $this->artisan('tracker:units-sync')->assertSuccessful();

        $renamed = $this->unitFor(Unit::LEGACY_PLATOON, $platoon->id);
        $this->assertSame($unitId, $renamed->id);
        $this->assertSame('New name', $renamed->name);
        $this->assertFalse(Unit::withTrashed()->where('legacy_type', Unit::LEGACY_SQUAD)->where('legacy_id', $doomed->id)->exists());
        $this->artisan('tracker:units-verify')->assertSuccessful();
    }

    #[Test]
    public function levels_use_the_divisions_unit_names_and_are_not_overwritten(): void
    {
        $division = $this->createActiveDivision();
        $division->settings()->set('locality', [
            ['old-string' => 'squad', 'new-string' => 'fireteam'],
            ['old-string' => 'platoon', 'new-string' => 'company'],
            ['old-string' => 'squad leader', 'new-string' => 'team lead'],
            ['old-string' => 'platoon leader', 'new-string' => 'company commander'],
        ]);

        $this->artisan('tracker:units-sync')->assertSuccessful();

        $levels = DivisionUnitLevel::where('division_id', $division->id)->orderBy('depth')->get();
        $this->assertSame(['Company', 'Companies', 'Company Commander'], [$levels[0]->label, $levels[0]->label_plural, $levels[0]->leader_title]);
        $this->assertSame(['Fireteam', 'Fireteams', 'Team Lead'], [$levels[1]->label, $levels[1]->label_plural, $levels[1]->leader_title]);

        $levels[0]->update(['label' => 'Battalion']);
        $this->artisan('tracker:units-sync')->assertSuccessful();

        $this->assertSame('Battalion', $levels[0]->fresh()->label);
    }

    #[Test]
    public function verify_fails_when_a_member_points_at_the_wrong_unit(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $other    = $this->createPlatoon($division);
        $member   = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id]);

        $this->artisan('tracker:units-sync')->assertSuccessful();
        Member::whereKey($member->id)->update(['unit_id' => $this->unitFor(Unit::LEGACY_PLATOON, $other->id)->id]);

        $this->artisan('tracker:units-verify')->assertFailed();
    }

    private function unitFor(string $type, int $id): Unit
    {
        return Unit::where('legacy_type', $type)->where('legacy_id', $id)->sole();
    }
}
