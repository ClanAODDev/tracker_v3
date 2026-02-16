<?php

namespace Tests\Feature\Reports;

use App\Enums\ActivityType;
use App\Enums\DiscordStatus;
use App\Models\Activity;
use App\Models\Census;
use App\Models\Division;
use App\Models\Member;
use App\Models\RankAction;
use App\Models\User;
use App\Repositories\DivisionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionReportControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_census_report_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division.census', $division->slug));

        $response->assertRedirect('/login');
    }

    public function test_retention_report_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division.retention-report', $division->slug));

        $response->assertRedirect('/login');
    }

    public function test_voice_report_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division.voice-report', $division->slug));

        $response->assertRedirect('/login');
    }

    public function test_promotions_report_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division.promotions', $division->slug));

        $response->assertRedirect('/login');
    }

    public function test_division_repository_recruits_query_uses_enum_value()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->createRecruitActivities($division, $officer, 4);

        $repository = app(DivisionRepository::class);
        $start      = now()->subMonths(6)->format('Y-m-d');
        $end        = now()->endOfMonth()->format('Y-m-d');

        $recruits = $repository->recruitsLast6Months($division->id, $start, $end);

        $this->assertGreaterThan(0, $recruits->sum('recruits'));
        $this->assertEquals(4, $recruits->sum('recruits'));
    }

    public function test_division_repository_removals_query_uses_enum_value()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->createRemovalActivities($division, $officer, 3);

        $repository = app(DivisionRepository::class);
        $start      = now()->subMonths(6)->format('Y-m-d');
        $end        = now()->endOfMonth()->format('Y-m-d');

        $removals = $repository->removalsLast6Months($division->id, $start, $end);

        $this->assertGreaterThan(0, $removals->sum('removals'));
        $this->assertEquals(3, $removals->sum('removals'));
    }

    public function test_division_repository_population_query_returns_census_data()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        Census::factory()->create([
            'division_id' => $division->id,
            'count'       => 150,
            'created_at'  => now()->subMonth(),
        ]);

        Census::factory()->create([
            'division_id' => $division->id,
            'count'       => 175,
            'created_at'  => now(),
        ]);

        $repository = app(DivisionRepository::class);
        $start      = now()->subMonths(6)->format('Y-m-d');
        $end        = now()->endOfMonth()->format('Y-m-d');

        $population = $repository->populationLast6Months($division->id, $start, $end);

        $this->assertGreaterThan(0, $population->count());
    }

    public function test_activity_queries_work_with_integer_backed_enum()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        Activity::create([
            'name'         => ActivityType::RECRUITED,
            'subject_type' => Member::class,
            'subject_id'   => $member->id,
            'user_id'      => $officer->id,
            'division_id'  => $division->id,
            'created_at'   => now(),
        ]);

        $activity = Activity::where('name', ActivityType::RECRUITED)
            ->where('division_id', $division->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertInstanceOf(ActivityType::class, $activity->name);
        $this->assertEquals(ActivityType::RECRUITED, $activity->name);
        $this->assertEquals(1, $activity->getRawOriginal('name'));
    }

    public function test_activity_removed_enum_stores_correct_integer()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        Activity::create([
            'name'         => ActivityType::REMOVED,
            'subject_type' => Member::class,
            'subject_id'   => $member->id,
            'user_id'      => $officer->id,
            'division_id'  => $division->id,
            'created_at'   => now(),
        ]);

        $activity = Activity::where('name', ActivityType::REMOVED)
            ->where('division_id', $division->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals(ActivityType::REMOVED, $activity->name);
        $this->assertEquals(3, $activity->getRawOriginal('name'));
    }

    public function test_recruits_and_removals_can_be_queried_together()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->createRecruitActivities($division, $officer, 5);
        $this->createRemovalActivities($division, $officer, 2);

        $repository = app(DivisionRepository::class);
        $start      = now()->subMonths(6)->format('Y-m-d');
        $end        = now()->endOfMonth()->format('Y-m-d');

        $recruits = $repository->recruitsLast6Months($division->id, $start, $end);
        $removals = $repository->removalsLast6Months($division->id, $start, $end);

        $this->assertEquals(5, $recruits->sum('recruits'));
        $this->assertEquals(2, $removals->sum('removals'));
        $this->assertEquals(3, $recruits->sum('recruits') - $removals->sum('removals'));
    }

    public function test_recruits_query_filters_by_date_range()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->createRecruitActivities($division, $officer, 3, now()->subMonths(2));
        $this->createRecruitActivities($division, $officer, 2, now()->subMonths(8));

        $repository = app(DivisionRepository::class);
        $start      = now()->subMonths(3)->format('Y-m-d');
        $end        = now()->format('Y-m-d');

        $recruits = $repository->recruitsLast6Months($division->id, $start, $end);

        $this->assertEquals(3, $recruits->sum('recruits'));
    }

    public function test_removals_query_filters_by_date_range()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->createRemovalActivities($division, $officer, 4, now()->subMonths(1));
        $this->createRemovalActivities($division, $officer, 6, now()->subMonths(10));

        $repository = app(DivisionRepository::class);
        $start      = now()->subMonths(3)->format('Y-m-d');
        $end        = now()->format('Y-m-d');

        $removals = $repository->removalsLast6Months($division->id, $start, $end);

        $this->assertEquals(4, $removals->sum('removals'));
    }

    public function test_recruits_query_filters_by_division()
    {
        $officer       = $this->createOfficer();
        $division      = $officer->member->division;
        $otherDivision = $this->createActiveDivision();

        $this->createRecruitActivities($division, $officer, 3);
        $this->createRecruitActivities($otherDivision, $officer, 5);

        $repository = app(DivisionRepository::class);
        $start      = now()->subMonths(6)->format('Y-m-d');
        $end        = now()->endOfMonth()->format('Y-m-d');

        $recruits = $repository->recruitsLast6Months($division->id, $start, $end);

        $this->assertEquals(3, $recruits->sum('recruits'));
    }

    public function test_rank_action_approved_can_be_queried()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        RankAction::factory()->approved()->create([
            'member_id'   => $member->id,
            'approved_at' => now(),
        ]);

        $approvedActions = RankAction::whereNotNull('approved_at')
            ->whereHas('member', fn ($q) => $q->where('division_id', $division->id))
            ->get();

        $this->assertEquals(1, $approvedActions->count());
    }

    public function test_census_data_can_be_created_and_queried()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        Census::factory()->create([
            'division_id'        => $division->id,
            'count'              => 100,
            'weekly_voice_count' => 75,
            'created_at'         => now(),
        ]);

        $census = Census::where('division_id', $division->id)->first();

        $this->assertNotNull($census);
        $this->assertEquals(100, $census->count);
        $this->assertEquals(75, $census->weekly_voice_count);
    }

    public function test_members_with_discord_issues_can_be_queried()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        Member::factory()->create([
            'division_id'       => $division->id,
            'last_voice_status' => DiscordStatus::DISCONNECTED,
        ]);

        Member::factory()->create([
            'division_id'       => $division->id,
            'last_voice_status' => DiscordStatus::NEVER_CONNECTED,
        ]);

        Member::factory()->create([
            'division_id'       => $division->id,
            'last_voice_status' => DiscordStatus::CONNECTED,
        ]);

        $disconnectedCount = Member::where('division_id', $division->id)
            ->whereIn('last_voice_status', [
                DiscordStatus::DISCONNECTED,
                DiscordStatus::NEVER_CONNECTED,
                DiscordStatus::NEVER_CONFIGURED,
            ])
            ->count();

        $this->assertEquals(2, $disconnectedCount);
    }

    private function createRecruitActivities(Division $division, User $officer, int $count, $date = null): void
    {
        $date = $date ?? now();

        for ($i = 0; $i < $count; $i++) {
            $member = $this->createMember(['division_id' => $division->id]);

            Activity::create([
                'name'         => ActivityType::RECRUITED,
                'subject_type' => Member::class,
                'subject_id'   => $member->id,
                'user_id'      => $officer->id,
                'division_id'  => $division->id,
                'created_at'   => $date,
            ]);
        }
    }

    private function createRemovalActivities(Division $division, User $officer, int $count, $date = null): void
    {
        $date = $date ?? now();

        for ($i = 0; $i < $count; $i++) {
            $member = $this->createMember(['division_id' => $division->id]);

            Activity::create([
                'name'         => ActivityType::REMOVED,
                'subject_type' => Member::class,
                'subject_id'   => $member->id,
                'user_id'      => $officer->id,
                'division_id'  => $division->id,
                'created_at'   => $date,
            ]);
        }
    }
}
