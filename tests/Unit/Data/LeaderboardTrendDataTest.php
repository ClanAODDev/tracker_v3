<?php

namespace Tests\Unit\Data;

use App\Data\LeaderboardTrendData;
use App\Models\Division;
use App\Models\LeaderboardSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeaderboardTrendDataTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();
        $this->division = Division::factory()->create();
    }

    #[Test]
    public function it_returns_empty_trends_when_no_snapshots_exist(): void
    {
        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice');

        $this->assertTrue($trend->history->isEmpty());
        $this->assertNull($trend->bestRank);
        $this->assertNull($trend->worstRank);
        $this->assertNull($trend->averageRank);
        $this->assertEquals(0, $trend->longestStreakAtFirst);
        $this->assertEquals(0.0, $trend->volatility);
    }

    #[Test]
    public function it_calculates_best_and_worst_rank(): void
    {
        $this->createSnapshots([3, 1, 5, 2]);

        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice');

        $this->assertEquals(1, $trend->bestRank);
        $this->assertEquals(5, $trend->worstRank);
    }

    #[Test]
    public function it_calculates_average_rank(): void
    {
        $this->createSnapshots([2, 4, 6]);

        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice');

        $this->assertEquals(4.0, $trend->averageRank);
    }

    #[Test]
    public function it_calculates_longest_streak_at_first(): void
    {
        $this->createSnapshots([1, 1, 2, 1, 1, 1, 3]);

        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice');

        $this->assertEquals(3, $trend->longestStreakAtFirst);
    }

    #[Test]
    public function it_calculates_zero_streak_when_never_first(): void
    {
        $this->createSnapshots([2, 3, 4]);

        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice');

        $this->assertEquals(0, $trend->longestStreakAtFirst);
    }

    #[Test]
    public function it_calculates_volatility(): void
    {
        $this->createSnapshots([1, 3, 1, 3]);

        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice');

        $this->assertEquals(2.0, $trend->volatility);
    }

    #[Test]
    public function it_returns_zero_volatility_for_single_snapshot(): void
    {
        $this->createSnapshots([1]);

        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice');

        $this->assertEquals(0.0, $trend->volatility);
    }

    #[Test]
    public function it_limits_history_to_requested_weeks(): void
    {
        $this->createSnapshots(range(1, 20));

        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice', weeks: 5);

        $this->assertCount(5, $trend->history);
    }

    #[Test]
    public function it_returns_history_in_chronological_order(): void
    {
        $this->createSnapshots([3, 1, 2]);

        $trend = LeaderboardTrendData::forDivision($this->division->id, 'voice');

        $ranks = $trend->history->pluck('rank')->all();
        $this->assertEquals([3, 1, 2], $ranks);
    }

    #[Test]
    public function it_only_includes_snapshots_for_requested_category(): void
    {
        LeaderboardSnapshot::factory()->voice()->create([
            'division_id' => $this->division->id,
            'rank' => 1,
            'snapshot_date' => '2026-07-01',
        ]);
        LeaderboardSnapshot::factory()->growth()->create([
            'division_id' => $this->division->id,
            'rank' => 5,
            'snapshot_date' => '2026-07-01',
        ]);

        $voiceTrend = LeaderboardTrendData::forDivision($this->division->id, 'voice');
        $growthTrend = LeaderboardTrendData::forDivision($this->division->id, 'growth');

        $this->assertEquals(1, $voiceTrend->bestRank);
        $this->assertEquals(5, $growthTrend->bestRank);
    }

    private function createSnapshots(array $ranks): void
    {
        foreach ($ranks as $i => $rank) {
            LeaderboardSnapshot::factory()->voice()->create([
                'division_id' => $this->division->id,
                'rank' => $rank,
                'snapshot_date' => now()->subWeeks(count($ranks) - $i - 1)->toDateString(),
            ]);
        }
    }
}
