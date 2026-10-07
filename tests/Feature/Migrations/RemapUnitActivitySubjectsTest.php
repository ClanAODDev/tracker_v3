<?php

namespace Tests\Feature\Migrations;

use App\Models\Platoon;
use App\Models\Squad;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class RemapUnitActivitySubjectsTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    #[Test]
    public function platoon_and_squad_activity_points_at_their_units_and_back(): void
    {
        $platoon = $this->createPlatoon();
        $squad   = $this->createSquad($platoon);
        $rows    = [
            $this->activity(Platoon::class, $platoon->id),
            $this->activity(Squad::class, $squad->id),
            $this->activity('App\Models\Member', $squad->id),
        ];

        $migration = require database_path('migrations/2026_10_07_000002_remap_unit_activity_subjects.php');
        $migration->up();

        $this->assertSame([Unit::class, $this->unitFor($platoon)->id], $this->subject($rows[0]));
        $this->assertSame([Unit::class, $this->unitFor($squad)->id], $this->subject($rows[1]));
        $this->assertSame(['App\Models\Member', $squad->id], $this->subject($rows[2]));

        $migration->down();

        $this->assertSame([Platoon::class, $platoon->id], $this->subject($rows[0]));
        $this->assertSame([Squad::class, $squad->id], $this->subject($rows[1]));
    }

    private function activity(string $type, int $id): int
    {
        return DB::table('activities')->insertGetId([
            'subject_type' => $type,
            'subject_id'   => $id,
            'name'         => 'updated_squad',
            'user_id'      => 1,
            'division_id'  => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function subject(int $id): array
    {
        $row = DB::table('activities')->find($id);

        return [$row->subject_type, (int) $row->subject_id];
    }
}
