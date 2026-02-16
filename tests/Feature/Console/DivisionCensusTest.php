<?php

namespace Tests\Feature\Console;

use App\Models\Census;
use App\Models\Division;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DivisionCensusTest extends TestCase
{
    use RefreshDatabase;

    public function test_census_command_records_data_for_active_divisions(): void
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

    public function test_census_command_skips_when_already_performed_today(): void
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

    public function test_census_command_runs_with_force_option(): void
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

    public function test_census_command_runs_when_last_census_was_yesterday(): void
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

    public function test_census_handles_no_active_divisions(): void
    {
        Division::factory()->inactive()->create();

        $this->artisan('tracker:census')
            ->assertSuccessful()
            ->expectsOutput('No active divisions found.');
    }
}
