<?php

namespace Tests\Feature\Console;

use App\Models\Census;
use App\Models\Division;
use App\Models\LeaderboardSnapshot;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LeaderboardSnapshotTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function snapshot_command_creates_entries_for_active_divisions(): void
    {
        $this->seedLeaderboardDivisions(3);

        $this->artisan('tracker:leaderboard-snapshot')
            ->assertSuccessful()
            ->expectsOutputToContain('Snapshot recorded');

        $this->assertEquals(9, LeaderboardSnapshot::count());

        foreach (['voice', 'growth', 'recruits'] as $category) {
            $this->assertEquals(3, LeaderboardSnapshot::where('category', $category)->count());
        }
    }

    #[Test]
    public function snapshot_command_excludes_shutting_down_divisions(): void
    {
        $divisions = $this->seedLeaderboardDivisions(3);
        $divisions->first()->update(['shutdown_at' => now()]);

        $this->artisan('tracker:leaderboard-snapshot')->assertSuccessful();

        $this->assertEquals(6, LeaderboardSnapshot::count());
        $this->assertEquals(
            0,
            LeaderboardSnapshot::forDivision($divisions->first()->id)->count()
        );
    }

    #[Test]
    public function snapshot_skips_when_already_exists_today(): void
    {
        $division = $this->seedLeaderboardDivisions(1)->first();

        LeaderboardSnapshot::factory()->create([
            'division_id'   => $division->id,
            'snapshot_date' => today()->toDateString(),
        ]);

        $this->artisan('tracker:leaderboard-snapshot')
            ->assertSuccessful()
            ->expectsOutput('Snapshot already exists for today. Use --force to overwrite.');

        $this->assertEquals(1, LeaderboardSnapshot::count());
    }

    #[Test]
    public function snapshot_force_replaces_existing_entries(): void
    {
        $this->seedLeaderboardDivisions(2);

        $this->artisan('tracker:leaderboard-snapshot')->assertSuccessful();
        $firstCount = LeaderboardSnapshot::count();

        $this->artisan('tracker:leaderboard-snapshot --force')->assertSuccessful();

        $this->assertEquals($firstCount, LeaderboardSnapshot::count());
    }

    #[Test]
    public function snapshot_computes_rank_change_from_previous_snapshot(): void
    {
        $divisions = $this->seedLeaderboardDivisions(3);

        $previousDate = today()->subWeek()->toDateString();

        foreach ($divisions as $index => $division) {
            LeaderboardSnapshot::factory()->voice()->create([
                'division_id'   => $division->id,
                'rank'          => $index + 1,
                'snapshot_date' => $previousDate,
            ]);
        }

        $this->artisan('tracker:leaderboard-snapshot')->assertSuccessful();

        $todaySnapshots = LeaderboardSnapshot::where('snapshot_date', today()->toDateString())
            ->where('category', 'voice')
            ->get();

        foreach ($todaySnapshots as $snapshot) {
            $this->assertNotNull($snapshot->previous_rank);
        }
    }

    #[Test]
    public function first_snapshot_has_null_previous_rank_and_zero_change(): void
    {
        $this->seedLeaderboardDivisions(2);

        $this->artisan('tracker:leaderboard-snapshot')->assertSuccessful();

        $snapshots = LeaderboardSnapshot::all();
        foreach ($snapshots as $snapshot) {
            $this->assertNull($snapshot->previous_rank);
            $this->assertEquals(0, $snapshot->rank_change);
        }
    }

    #[Test]
    public function snapshot_is_idempotent_with_force_on_same_day(): void
    {
        $this->seedLeaderboardDivisions(2);

        $this->artisan('tracker:leaderboard-snapshot')->assertSuccessful();
        $this->artisan('tracker:leaderboard-snapshot --force')->assertSuccessful();

        $todayCount = LeaderboardSnapshot::where('snapshot_date', today()->toDateString())->count();
        $this->assertEquals(6, $todayCount);
    }

    #[Test]
    public function snapshot_assigns_sequential_ranks(): void
    {
        $this->seedLeaderboardDivisions(4);

        $this->artisan('tracker:leaderboard-snapshot')->assertSuccessful();

        $voiceRanks = LeaderboardSnapshot::where('category', 'voice')
            ->where('snapshot_date', today()->toDateString())
            ->orderBy('rank')
            ->pluck('rank')
            ->all();

        $this->assertEquals([1, 2, 3, 4], $voiceRanks);
    }

    private function seedLeaderboardDivisions(int $count): Collection
    {
        $divisions = Division::factory()->count($count)->create();

        foreach ($divisions as $division) {
            Member::factory()->count(rand(5, 20))->create([
                'division_id' => $division->id,
                'join_date'   => now()->subDays(rand(1, 60)),
            ]);

            Census::factory()->count(3)->create([
                'division_id'        => $division->id,
                'weekly_voice_count' => rand(10, 50),
            ]);
        }

        return $divisions;
    }
}
