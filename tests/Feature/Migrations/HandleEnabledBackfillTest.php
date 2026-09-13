<?php

namespace Tests\Feature\Migrations;

use App\Models\Division;
use App\Models\Handle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HandleEnabledBackfillTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): object
    {
        return include database_path('migrations/2026_09_13_173754_add_enabled_to_handles_table.php');
    }

    #[Test]
    public function it_keeps_handles_required_by_a_genuinely_active_division_enabled()
    {
        // The column always starts `true` (schema default); the backfill only ever
        // demotes rows to `false`, so this seeds the pre-backfill state accurately.
        $handle = Handle::factory()->create(['enabled' => true]);
        Division::factory()->create(['active' => true, 'handle_id' => $handle->id]);

        $this->migration()->backfillEnabledFlag();

        $this->assertTrue($handle->fresh()->enabled);
    }

    #[Test]
    public function it_disables_handles_with_no_division_at_all()
    {
        $handle = Handle::factory()->create(['enabled' => true]);

        $this->migration()->backfillEnabledFlag();

        $this->assertFalse($handle->fresh()->enabled);
    }

    #[Test]
    public function it_disables_handles_only_required_by_an_inactive_division()
    {
        $handle = Handle::factory()->create(['enabled' => true]);
        Division::factory()->create(['active' => false, 'handle_id' => $handle->id]);

        $this->migration()->backfillEnabledFlag();

        $this->assertFalse($handle->fresh()->enabled);
    }

    #[Test]
    public function it_disables_handles_only_required_by_a_soft_deleted_division_marked_active()
    {
        $handle   = Handle::factory()->create(['enabled' => true]);
        $division = Division::factory()->create(['active' => true, 'handle_id' => $handle->id]);
        $division->delete();

        $this->migration()->backfillEnabledFlag();

        $this->assertFalse($handle->fresh()->enabled);
    }
}
