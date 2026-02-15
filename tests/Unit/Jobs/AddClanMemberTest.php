<?php

namespace Tests\Unit\Jobs;

use App\Enums\ForumGroup;
use App\Jobs\AddClanMember;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AddClanMemberTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private ForumProcedureService $procedureService;

    protected function setUp(): void
    {
        parent::setUp();
        config(['aod.token' => 'test-token']);
        $this->procedureService = Mockery::mock(ForumProcedureService::class);
        $this->procedureService->shouldReceive('getUser')->byDefault()->andReturn(null);
    }

    public function test_job_can_be_instantiated()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

        $job = new AddClanMember($member, 12345);

        $this->assertInstanceOf(AddClanMember::class, $job);
    }

    public function test_job_calls_forum_service_with_correct_parameters()
    {
        Http::fake([
            '*' => Http::response('saved_user_x_successfully', 200),
        ]);

        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);
        $adminId = 12345;

        $job = new AddClanMember($member, $adminId);
        $job->handle($this->procedureService);

        Http::assertSent(function ($request) use ($member, $adminId) {
            $url = $request->url();

            return str_contains($url, "aod_userid={$adminId}")
                && str_contains($url, "u={$member->clan_id}")
                && str_contains($url, 'do=addaod');
        });
    }

    public function test_job_includes_aod_prefix_in_member_name()
    {
        Http::fake([
            '*' => Http::response('saved_user_x_successfully', 200),
        ]);

        $division = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $division->id,
            'name' => 'TestPlayer',
        ]);

        $job = new AddClanMember($member, 12345);
        $job->handle($this->procedureService);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), urlencode('AOD_TestPlayer'));
        });
    }

    public function test_job_throws_exception_on_failure()
    {
        Http::fake([
            '*' => Http::response('error_invalid_user', 200),
        ]);

        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

        $job = new AddClanMember($member, 12345);

        $this->expectException(\RuntimeException::class);
        $job->handle($this->procedureService);
    }

    public function test_job_diagnoses_banned_user_on_failure()
    {
        Http::fake([
            '*' => Http::response('error_invalid_user', 200),
        ]);

        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

        $this->procedureService->shouldReceive('getUser')
            ->with($member->clan_id)
            ->andReturn((object) ['usergroupid' => ForumGroup::BANNED->value]);

        $job = new AddClanMember($member, 12345);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('banned');
        $job->handle($this->procedureService);
    }

    public function test_job_diagnoses_awaiting_email_on_failure()
    {
        Http::fake([
            '*' => Http::response('error_invalid_user', 200),
        ]);

        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

        $this->procedureService->shouldReceive('getUser')
            ->with($member->clan_id)
            ->andReturn((object) ['usergroupid' => ForumGroup::AWAITING_EMAIL_CONFIRMATION->value]);

        $job = new AddClanMember($member, 12345);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not verified their email');
        $job->handle($this->procedureService);
    }
}
