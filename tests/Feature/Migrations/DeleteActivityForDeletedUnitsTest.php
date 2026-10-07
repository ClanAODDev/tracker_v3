<?php

namespace Tests\Feature\Migrations;

use App\Models\Platoon;
use App\Models\Squad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class DeleteActivityForDeletedUnitsTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    #[Test]
    public function only_activity_for_hard_deleted_units_is_removed(): void
    {
        $platoon      = $this->createPlatoon();
        $squad        = $this->createSquad($platoon);
        $trashedSquad = $this->createSquad($platoon);
        $trashedSquad->delete();
        $deletedPlatoon = 999990;
        $deletedSquad   = 999991;

        $keep = [
            $this->activity(Platoon::class, $platoon->id),
            $this->activity(Squad::class, $squad->id),
            $this->activity(Squad::class, $trashedSquad->id),
            $this->activity('App\Models\Member', $deletedSquad),
        ];
        $remove = [
            $this->activity(Platoon::class, $deletedPlatoon),
            $this->activity(Squad::class, $deletedSquad),
        ];

        $this->runMigration();

        $remaining = DB::table('activities')->pluck('id')->all();
        $this->assertEqualsCanonicalizing($keep, $remaining);
        $this->assertEmpty(array_intersect($remove, $remaining));
    }

    private function activity(string $type, int $id): int
    {
        return DB::table('activities')->insertGetId([
            'subject_type' => $type,
            'subject_id'   => $id,
            'name'         => 'created_squad',
            'user_id'      => 1,
            'division_id'  => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function runMigration(): void
    {
        (require database_path('migrations/2026_10_06_000002_delete_activity_for_deleted_units.php'))->up();
    }
}
