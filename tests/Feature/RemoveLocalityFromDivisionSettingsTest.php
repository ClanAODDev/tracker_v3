<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class RemoveLocalityFromDivisionSettingsTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    #[Test]
    public function the_migration_strips_only_the_locality_key(): void
    {
        $division = $this->createActiveDivision();

        DB::table('divisions')->where('id', $division->id)->update([
            'settings' => json_encode(['locality' => [['old-string' => 'squad', 'new-string' => 'team']], 'welcome_area' => 'hi']),
        ]);

        (require database_path('migrations/2026_10_08_000002_remove_locality_from_division_settings.php'))->up();

        $this->assertSame(['welcome_area' => 'hi'], $division->fresh()->settings);
    }
}
