<?php

namespace Tests\Feature\Migrations;

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

    private const PLATOON = 'App\Models\Platoon';

    private const SQUAD = 'App\Models\Squad';

    #[Test]
    public function platoon_and_squad_activity_points_at_their_units_and_back(): void
    {
        $platoon = $this->createPlatoon();
        $squad   = $this->createSquad($platoon);
        $platoon->update(['legacy_type' => Unit::LEGACY_PLATOON, 'legacy_id' => 31]);
        $squad->update(['legacy_type' => Unit::LEGACY_SQUAD, 'legacy_id' => 52]);
        $rows = [
            $this->activity(self::PLATOON, 31),
            $this->activity(self::SQUAD, 52),
            $this->activity('App\Models\Member', 52),
        ];

        $migration = require database_path('migrations/2026_10_07_000002_remap_unit_activity_subjects.php');
        $migration->up();

        $this->assertSame([Unit::class, $platoon->id], $this->subject($rows[0]));
        $this->assertSame([Unit::class, $squad->id], $this->subject($rows[1]));
        $this->assertSame(['App\Models\Member', 52], $this->subject($rows[2]));

        $migration->down();

        $this->assertSame([self::PLATOON, 31], $this->subject($rows[0]));
        $this->assertSame([self::SQUAD, 52], $this->subject($rows[1]));
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
