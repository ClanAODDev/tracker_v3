<?php

namespace Tests\Unit\Models;

use App\Models\ClanSnapshot;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClanSnapshotTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_casts_snapshot_date_to_date(): void
    {
        $snapshot = ClanSnapshot::factory()->create([
            'snapshot_date' => '2026-07-10',
        ]);

        $this->assertInstanceOf(Carbon::class, $snapshot->snapshot_date);
        $this->assertEquals('2026-07-10', $snapshot->snapshot_date->toDateString());
    }

    #[Test]
    public function scope_recent_orders_by_snapshot_date_descending(): void
    {
        ClanSnapshot::factory()->create(['snapshot_date' => '2026-07-01']);
        ClanSnapshot::factory()->create(['snapshot_date' => '2026-07-15']);
        ClanSnapshot::factory()->create(['snapshot_date' => '2026-07-08']);

        $dates = ClanSnapshot::recent()->pluck('snapshot_date')
            ->map->toDateString()
            ->all();

        $this->assertEquals(['2026-07-15', '2026-07-08', '2026-07-01'], $dates);
    }

    #[Test]
    public function snapshot_date_is_unique(): void
    {
        ClanSnapshot::factory()->create(['snapshot_date' => '2026-07-10']);

        $this->expectException(QueryException::class);

        ClanSnapshot::factory()->create(['snapshot_date' => '2026-07-10']);
    }
}
