<?php

namespace Tests\Feature\Console;

use App\Enums\Position;
use App\Models\Member;
use App\Models\Squad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitsPreflightTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_reports_anomalies_by_id_without_changing_anything(): void
    {
        $divisionA = $this->createActiveDivision();
        $divisionB = $this->createActiveDivision();
        $platoonA  = $this->createPlatoon($divisionA);
        $squadA    = $this->createSquad($platoonA);
        $orphan    = Squad::factory()->create(['platoon_id' => 999999]);
        $misplaced = $this->createMember(['division_id' => $divisionB->id, 'platoon_id' => $platoonA->id, 'squad_id' => $squadA->id]);
        $stale     = $this->createMember(['division_id' => $divisionB->id, 'position' => Position::MEMBER]);
        $platoonA->update(['leader_id' => $stale->clan_id]);
        $before = Member::all()->toArray();

        Artisan::call('tracker:units-preflight', ['--json' => true]);
        $report = collect(json_decode(Artisan::output(), true))->keyBy('key');

        $this->assertSame([$orphan->id], $report['orphan_squads']['ids']);
        $this->assertSame([$misplaced->id], $report['platoon_in_other_division']['ids']);
        $this->assertSame([$stale->id], $report['leader_other_division']['ids']);
        $this->assertSame([$stale->id], $report['leader_wrong_position']['ids']);
        $this->assertSame([$stale->id], $report['leader_not_assigned_to_unit']['ids']);
        $this->assertSame([], $report['missing_squad']['ids']);
        $this->assertSame($before, Member::all()->toArray());
        $this->assertSame($stale->clan_id, $platoonA->fresh()->leader_id);
    }
}
