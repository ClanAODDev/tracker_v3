<?php

namespace Tests\Feature;

use App\Data\DivisionStatsData;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DivisionStatsDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function returns_zero_counts_for_division_with_no_members(): void
    {
        $division = Division::factory()->create();

        $stats = DivisionStatsData::fromDivision($division);

        $this->assertEquals(0, $stats->memberCount);
        $this->assertEquals(0, $stats->voiceActiveCount);
        $this->assertEquals(0, $stats->voiceRate);
        $this->assertEquals(0, $stats->recruitsThisMonth);
    }

    #[Test]
    public function counts_total_members(): void
    {
        $division = Division::factory()->create();
        Member::factory()->count(5)->create(['division_id' => $division->id]);

        $stats = DivisionStatsData::fromDivision($division);

        $this->assertEquals(5, $stats->memberCount);
    }

    #[Test]
    public function counts_voice_active_members_within_threshold(): void
    {
        $division              = Division::factory()->create();
        $activityThresholdDays = $division->settings()->get('inactivity_days') ?? 30;

        Member::factory()->count(2)->create([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays($activityThresholdDays - 1),
        ]);
        Member::factory()->create([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays($activityThresholdDays + 1),
        ]);

        $stats = DivisionStatsData::fromDivision($division);

        $this->assertEquals(3, $stats->memberCount);
        $this->assertEquals(2, $stats->voiceActiveCount);
    }

    #[Test]
    public function calculates_voice_rate_as_percentage(): void
    {
        $division = Division::factory()->create();

        Member::factory()->count(2)->create([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays(1),
        ]);
        Member::factory()->count(2)->create([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subDays(60),
        ]);

        $stats = DivisionStatsData::fromDivision($division);

        $this->assertEquals(50, $stats->voiceRate);
    }

    #[Test]
    public function counts_recruits_joined_in_last_30_days(): void
    {
        $division = Division::factory()->create();

        Member::factory()->count(2)->create([
            'division_id' => $division->id,
            'join_date'   => now()->startOfMonth()->addDays(1),
        ]);
        Member::factory()->create([
            'division_id' => $division->id,
            'join_date'   => now()->subMonths(2),
        ]);

        $stats = DivisionStatsData::fromDivision($division);

        $this->assertEquals(2, $stats->recruitsThisMonth);
    }

    #[Test]
    public function voice_rate_is_zero_when_no_members(): void
    {
        $division = Division::factory()->create();

        $stats = DivisionStatsData::fromDivision($division);

        $this->assertEquals(0, $stats->voiceRate);
    }
}
