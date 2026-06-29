<?php

namespace Tests\Feature\Console;

use App\Models\Census;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DivisionCensusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function census_command_records_data_for_active_divisions(): void
    {
        $division = Division::factory()->create();

        $this->artisan('tracker:census')
            ->assertSuccessful()
            ->expectsOutputToContain('Beginning division census')
            ->expectsOutputToContain('Census complete. Recorded: 1');

        $this->assertDatabaseHas('censuses', [
            'division_id' => $division->id,
        ]);
    }

    #[Test]
    public function census_command_skips_when_already_performed_today(): void
    {
        $division = Division::factory()->create();

        Census::factory()->create([
            'division_id' => $division->id,
            'created_at'  => now(),
        ]);

        $this->artisan('tracker:census')
            ->assertSuccessful()
            ->expectsOutput('Census already performed today. Use --force to run anyway.');

        $this->assertDatabaseCount('censuses', 1);
    }

    #[Test]
    public function census_command_runs_with_force_option(): void
    {
        $division = Division::factory()->create();

        Census::factory()->create([
            'division_id' => $division->id,
            'created_at'  => now(),
        ]);

        $this->artisan('tracker:census --force')
            ->assertSuccessful()
            ->expectsOutputToContain('Census complete. Recorded: 1');

        $this->assertDatabaseCount('censuses', 2);
    }

    #[Test]
    public function census_command_runs_when_last_census_was_yesterday(): void
    {
        $division = Division::factory()->create();

        Census::factory()->create([
            'division_id' => $division->id,
            'created_at'  => now()->subDay(),
        ]);

        $this->artisan('tracker:census')
            ->assertSuccessful()
            ->expectsOutputToContain('Census complete.');

        $this->assertDatabaseCount('censuses', 2);
    }

    #[Test]
    public function census_handles_no_active_divisions(): void
    {
        Division::factory()->inactive()->create();

        $this->artisan('tracker:census')
            ->assertSuccessful()
            ->expectsOutput('No active divisions found.');
    }

    #[Test]
    public function census_records_correct_member_counts(): void
    {
        $division = Division::factory()->create();

        Member::factory()->count(3)->create(['division_id' => $division->id]);

        $this->artisan('tracker:census')->assertSuccessful();

        $this->assertDatabaseHas('censuses', [
            'division_id' => $division->id,
            'count'       => 3,
        ]);
    }

    #[Test]
    public function census_records_correct_voice_active_count(): void
    {
        $division = Division::factory()->create();

        Member::factory()->count(2)->create([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays(3),
        ]);
        Member::factory()->create([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays(30),
        ]);

        $this->artisan('tracker:census')->assertSuccessful();

        $this->assertDatabaseHas('censuses', [
            'division_id'        => $division->id,
            'count'              => 3,
            'weekly_voice_count' => 2,
        ]);
    }

    #[Test]
    public function census_records_zeros_for_division_with_no_members(): void
    {
        $division = Division::factory()->create();

        $this->artisan('tracker:census')->assertSuccessful();

        $this->assertDatabaseHas('censuses', [
            'division_id'         => $division->id,
            'count'               => 0,
            'weekly_active_count' => 0,
            'weekly_voice_count'  => 0,
        ]);
    }

    #[Test]
    public function census_records_all_divisions_in_one_operation(): void
    {
        Division::factory()->count(3)->create();

        $this->artisan('tracker:census')
            ->assertSuccessful()
            ->expectsOutputToContain('Census complete. Recorded: 3');

        $this->assertDatabaseCount('censuses', 3);
    }
}
