<?php

namespace Tests\Feature;

use App\Data\DivisionLeaderboardData;
use App\Models\Census;
use App\Models\Division;
use App\Models\LeaderboardSnapshot;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DivisionLeaderboardDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function calculate_returns_all_three_leader_categories(): void
    {
        $this->seedDivisionWithCensus();

        $result = DivisionLeaderboardData::calculate();

        $this->assertArrayHasKey('voiceLeaders', $result);
        $this->assertArrayHasKey('growthLeaders', $result);
        $this->assertArrayHasKey('recruitLeaders', $result);
    }

    #[Test]
    public function calculate_excludes_divisions_with_shutdown_at_set(): void
    {
        $activeDivision = $this->seedDivisionWithCensus();
        $shuttingDown   = $this->seedDivisionWithCensus();
        $shuttingDown->update(['shutdown_at' => now()]);

        $result = DivisionLeaderboardData::calculate();

        $this->assertTrue($result['voiceLeaders']->pluck('id')->contains($activeDivision->id));
        $this->assertFalse($result['voiceLeaders']->pluck('id')->contains($shuttingDown->id));
        $this->assertFalse($result['growthLeaders']->pluck('id')->contains($shuttingDown->id));
        $this->assertFalse($result['recruitLeaders']->pluck('id')->contains($shuttingDown->id));
    }

    #[Test]
    public function calculate_returns_divisions_sorted_by_value_descending(): void
    {
        $divA = $this->seedDivisionWithCensus(voiceCount: 10, totalCount: 100);
        $divB = $this->seedDivisionWithCensus(voiceCount: 80, totalCount: 100);

        $result   = DivisionLeaderboardData::calculate();
        $voiceIds = $result['voiceLeaders']->pluck('id')->all();

        $this->assertEquals($divB->id, $voiceIds[0]);
        $this->assertEquals($divA->id, $voiceIds[1]);
    }

    #[Test]
    public function for_user_enriches_with_movement_data_when_snapshots_exist(): void
    {
        $division = $this->seedDivisionWithCensus();
        $user     = $this->createUserWithMember($division);

        LeaderboardSnapshot::factory()->voice()->create([
            'division_id'   => $division->id,
            'rank'          => 3,
            'previous_rank' => 5,
            'rank_change'   => 2,
            'snapshot_date' => today()->toDateString(),
        ]);

        DivisionLeaderboardData::clearCache();

        $data       = DivisionLeaderboardData::forUser($user);
        $voiceEntry = $data->voiceLeaders->firstWhere('id', $division->id);

        $this->assertEquals(2, $voiceEntry['rank_change']);
        $this->assertEquals(3, $voiceEntry['previous_rank']);
    }

    #[Test]
    public function for_user_returns_zero_movement_when_no_snapshots(): void
    {
        $division = $this->seedDivisionWithCensus();
        $user     = $this->createUserWithMember($division);

        DivisionLeaderboardData::clearCache();

        $data       = DivisionLeaderboardData::forUser($user);
        $voiceEntry = $data->voiceLeaders->firstWhere('id', $division->id);

        $this->assertArrayNotHasKey('rank_change', $voiceEntry);
    }

    #[Test]
    public function for_user_sets_user_division_id(): void
    {
        $division = $this->seedDivisionWithCensus();
        $user     = $this->createUserWithMember($division);

        DivisionLeaderboardData::clearCache();

        $data = DivisionLeaderboardData::forUser($user);

        $this->assertEquals($division->id, $data->userDivisionId);
    }

    #[Test]
    public function clear_cache_invalidates_cached_leaderboard(): void
    {
        $division = $this->seedDivisionWithCensus();
        $user     = $this->createUserWithMember($division);

        DivisionLeaderboardData::forUser($user);
        DivisionLeaderboardData::clearCache();

        $this->seedDivisionWithCensus(voiceCount: 99, totalCount: 100);
        $data = DivisionLeaderboardData::forUser($user);

        $this->assertCount(2, $data->voiceLeaders);
    }

    private function seedDivisionWithCensus(int $voiceCount = 30, int $totalCount = 100): Division
    {
        $division = Division::factory()->create();

        Member::factory()->count($totalCount)->create([
            'division_id' => $division->id,
            'join_date'   => now()->subDays(rand(1, 60)),
        ]);

        Census::factory()->create([
            'division_id'         => $division->id,
            'count'               => $totalCount,
            'weekly_active_count' => $totalCount,
            'weekly_voice_count'  => $voiceCount,
        ]);

        return $division;
    }

    private function createUserWithMember(Division $division): User
    {
        $member = Member::factory()->create(['division_id' => $division->id]);

        return User::factory()->create(['member_id' => $member->id]);
    }
}
