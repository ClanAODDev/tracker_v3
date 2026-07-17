<?php

namespace Tests\Unit\Models;

use App\Models\Division;
use App\Models\LeaderboardSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeaderboardSnapshotTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_division(): void
    {
        $division = Division::factory()->create();
        $snapshot = LeaderboardSnapshot::factory()->create(['division_id' => $division->id]);

        $this->assertTrue($snapshot->division->is($division));
    }

    #[Test]
    public function it_casts_snapshot_date_to_date(): void
    {
        $snapshot = LeaderboardSnapshot::factory()->create([
            'snapshot_date' => '2026-07-10',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $snapshot->snapshot_date);
        $this->assertEquals('2026-07-10', $snapshot->snapshot_date->toDateString());
    }

    #[Test]
    public function it_casts_trend_data_to_array(): void
    {
        $trend = [10, 20, 30, 40];
        $snapshot = LeaderboardSnapshot::factory()->create([
            'trend_data' => $trend,
        ]);

        $snapshot->refresh();
        $this->assertEquals($trend, $snapshot->trend_data);
    }

    #[Test]
    public function scope_for_category_filters_correctly(): void
    {
        LeaderboardSnapshot::factory()->voice()->create();
        LeaderboardSnapshot::factory()->growth()->create();
        LeaderboardSnapshot::factory()->recruits()->create();

        $this->assertCount(1, LeaderboardSnapshot::forCategory('voice')->get());
        $this->assertCount(1, LeaderboardSnapshot::forCategory('growth')->get());
        $this->assertCount(1, LeaderboardSnapshot::forCategory('recruits')->get());
    }

    #[Test]
    public function scope_for_division_filters_correctly(): void
    {
        $division = Division::factory()->create();
        LeaderboardSnapshot::factory()->create(['division_id' => $division->id]);
        LeaderboardSnapshot::factory()->create();

        $this->assertCount(1, LeaderboardSnapshot::forDivision($division->id)->get());
    }

    #[Test]
    public function scope_recent_orders_by_snapshot_date_descending(): void
    {
        LeaderboardSnapshot::factory()->voice()->create(['snapshot_date' => '2026-07-01']);
        LeaderboardSnapshot::factory()->voice()->create(['snapshot_date' => '2026-07-15']);
        LeaderboardSnapshot::factory()->voice()->create(['snapshot_date' => '2026-07-08']);

        $dates = LeaderboardSnapshot::recent()->pluck('snapshot_date')
            ->map->toDateString()
            ->all();

        $this->assertEquals(['2026-07-15', '2026-07-08', '2026-07-01'], $dates);
    }
}
