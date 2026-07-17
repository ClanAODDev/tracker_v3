<?php

namespace Tests\Feature\Console;

use App\Models\Census;
use App\Models\ClanSnapshot;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClanSnapshotTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function snapshot_command_creates_aggregate_row(): void
    {
        $this->seedDivisionsWithCensus(3);

        $this->artisan('tracker:clan-snapshot')
            ->assertSuccessful()
            ->expectsOutputToContain('Clan snapshot recorded');

        $this->assertDatabaseCount('clan_snapshots', 1);
    }

    #[Test]
    public function snapshot_aggregates_member_counts_correctly(): void
    {
        $divA = Division::factory()->create();
        $divB = Division::factory()->create();

        Member::factory()->count(10)->create(['division_id' => $divA->id]);
        Member::factory()->count(20)->create(['division_id' => $divB->id]);

        Census::factory()->create([
            'division_id' => $divA->id,
            'count' => 10,
            'weekly_active_count' => 8,
            'weekly_voice_count' => 5,
        ]);
        Census::factory()->create([
            'division_id' => $divB->id,
            'count' => 20,
            'weekly_active_count' => 15,
            'weekly_voice_count' => 10,
        ]);

        $this->artisan('tracker:clan-snapshot')->assertSuccessful();

        $snapshot = ClanSnapshot::first();

        $this->assertEquals(30, $snapshot->total_members);
        $this->assertEquals(2, $snapshot->active_divisions);
        $this->assertEquals(23, $snapshot->weekly_active_count);
        $this->assertEquals(15, $snapshot->weekly_voice_count);
        $this->assertEquals(50.00, (float) $snapshot->voice_participation);
    }

    #[Test]
    public function snapshot_counts_monthly_recruits(): void
    {
        $division = Division::factory()->create();

        Member::factory()->count(5)->create([
            'division_id' => $division->id,
            'join_date' => now()->subDays(5),
        ]);
        Member::factory()->count(3)->create([
            'division_id' => $division->id,
            'join_date' => now()->subMonths(2),
        ]);

        Census::factory()->create(['division_id' => $division->id]);

        $this->artisan('tracker:clan-snapshot')->assertSuccessful();

        $this->assertEquals(5, ClanSnapshot::first()->monthly_recruits);
    }

    #[Test]
    public function snapshot_skips_when_already_exists_today(): void
    {
        $this->seedDivisionsWithCensus(1);

        ClanSnapshot::factory()->create(['snapshot_date' => today()->toDateString()]);

        $this->artisan('tracker:clan-snapshot')
            ->assertSuccessful()
            ->expectsOutput('Clan snapshot already exists for today. Use --force to overwrite.');

        $this->assertDatabaseCount('clan_snapshots', 1);
    }

    #[Test]
    public function snapshot_force_replaces_existing(): void
    {
        $this->seedDivisionsWithCensus(2);

        $this->artisan('tracker:clan-snapshot')->assertSuccessful();
        $this->artisan('tracker:clan-snapshot --force')->assertSuccessful();

        $this->assertDatabaseCount('clan_snapshots', 1);
    }

    #[Test]
    public function snapshot_handles_no_active_divisions(): void
    {
        Division::factory()->inactive()->create();

        $this->artisan('tracker:clan-snapshot')
            ->assertSuccessful()
            ->expectsOutput('No active divisions found.');

        $this->assertDatabaseCount('clan_snapshots', 0);
    }

    #[Test]
    public function snapshot_uses_latest_census_per_division(): void
    {
        $division = Division::factory()->create();
        Member::factory()->count(5)->create(['division_id' => $division->id]);

        Census::factory()->create([
            'division_id' => $division->id,
            'count' => 50,
            'weekly_voice_count' => 10,
            'created_at' => now()->subWeek(),
        ]);
        Census::factory()->create([
            'division_id' => $division->id,
            'count' => 100,
            'weekly_voice_count' => 30,
            'created_at' => now(),
        ]);

        $this->artisan('tracker:clan-snapshot')->assertSuccessful();

        $snapshot = ClanSnapshot::first();
        $this->assertEquals(100, $snapshot->total_members);
        $this->assertEquals(30, $snapshot->weekly_voice_count);
    }

    private function seedDivisionsWithCensus(int $count): void
    {
        $divisions = Division::factory()->count($count)->create();

        foreach ($divisions as $division) {
            Member::factory()->count(rand(5, 20))->create([
                'division_id' => $division->id,
            ]);

            Census::factory()->create([
                'division_id' => $division->id,
                'weekly_voice_count' => rand(10, 50),
            ]);
        }
    }
}
