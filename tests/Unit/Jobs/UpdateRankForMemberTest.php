<?php

namespace Tests\Unit\Jobs;

use App\Enums\Rank;
use App\Jobs\UpdateRankForMember;
use App\Models\RankAction;
use App\Notifications\Channel\NotifyDivisionMemberPromotion;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UpdateRankForMemberTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected ForumProcedureService $procedureService;

    protected function setUp(): void
    {
        parent::setUp();
        config(['aod.rank.update_forums' => false]);
        Notification::fake();

        $this->procedureService = Mockery::mock(ForumProcedureService::class);
        $this->procedureService->shouldReceive('setUserRank')->andReturn(null);
    }

    public function test_job_can_be_instantiated()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $action = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $job = new UpdateRankForMember($action);

        $this->assertInstanceOf(UpdateRankForMember::class, $job);
    }

    public function test_job_updates_member_rank()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $action = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $job = new UpdateRankForMember($action);
        $job->handle($this->procedureService);

        $member->refresh();
        $this->assertEquals(Rank::CORPORAL, $member->rank);
    }

    public function test_job_updates_last_promoted_at_on_promotion()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember([
            'division_id'      => $division->id,
            'rank'             => Rank::PRIVATE_FIRST_CLASS,
            'last_promoted_at' => null,
        ]);

        $action = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $job = new UpdateRankForMember($action);
        $job->handle($this->procedureService);

        $member->refresh();
        $this->assertNotNull($member->last_promoted_at);
    }

    public function test_job_does_not_update_last_promoted_at_on_demotion()
    {
        $division     = $this->createActiveDivision();
        $originalDate = now()->subYear();
        $member       = $this->createMember([
            'division_id'      => $division->id,
            'rank'             => Rank::CORPORAL,
            'last_promoted_at' => $originalDate,
        ]);

        $action = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
            'rank'      => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $job = new UpdateRankForMember($action);
        $job->handle($this->procedureService);

        $member->refresh();
        $this->assertEquals(
            $originalDate->format('Y-m-d'),
            $member->last_promoted_at->format('Y-m-d')
        );
    }

    public function test_job_sends_division_notification_on_promotion()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $action = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $job = new UpdateRankForMember($action);
        $job->handle($this->procedureService);

        Notification::assertSentTo($division, NotifyDivisionMemberPromotion::class);
    }

    public function test_job_does_not_send_promotion_notification_on_demotion()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::CORPORAL,
        ]);

        $action = RankAction::factory()->approved()->create([
            'member_id' => $member->id,
            'rank'      => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $job = new UpdateRankForMember($action);
        $job->handle($this->procedureService);

        Notification::assertNotSentTo($division, NotifyDivisionMemberPromotion::class);
    }
}
