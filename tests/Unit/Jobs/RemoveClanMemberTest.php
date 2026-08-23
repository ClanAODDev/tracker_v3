<?php

namespace Tests\Unit\Jobs;

use App\Jobs\RemoveClanMember;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RemoveClanMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['aod.token' => 'test-token']);
    }

    private function makeForumService(?object $forumUser = null): ForumProcedureService
    {
        $service = $this->createMock(ForumProcedureService::class);
        $service->method('getUser')->willReturn($forumUser);

        return $service;
    }

    #[Test]
    public function job_can_be_instantiated()
    {
        $job = new RemoveClanMember(12345, 67890);

        $this->assertInstanceOf(RemoveClanMember::class, $job);
    }

    #[Test]
    public function job_calls_forum_service_with_correct_parameters()
    {
        Http::fake([
            '*' => Http::response('saved_user_x_successfully', 200),
        ]);

        $memberIdBeingRemoved  = 12345;
        $impersonatingMemberId = 67890;

        $job = new RemoveClanMember($memberIdBeingRemoved, $impersonatingMemberId);
        $job->handle($this->makeForumService());

        Http::assertSent(function ($request) use ($memberIdBeingRemoved, $impersonatingMemberId) {
            $url = $request->url();

            return str_contains($url, "aod_userid={$impersonatingMemberId}")
                && str_contains($url, "u={$memberIdBeingRemoved}")
                && str_contains($url, 'do=remaod');
        });
    }

    #[Test]
    public function job_throws_exception_on_failure()
    {
        Http::fake([
            '*' => Http::response('error_user_not_found', 200),
        ]);

        $job = new RemoveClanMember(12345, 67890);

        $this->expectException(\RuntimeException::class);
        $job->handle($this->makeForumService());
    }

    #[Test]
    public function job_completes_without_throwing_when_forum_has_no_profile_for_member()
    {
        Http::fake([
            '*' => Http::response('invalid_user_specified', 200),
        ]);

        $job = new RemoveClanMember(12345, 67890);

        $job->handle($this->makeForumService(null));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'do=remaod'));
    }

    #[Test]
    public function job_completes_without_throwing_when_member_exists_but_is_already_outside_aod_usergroup()
    {
        Http::fake([
            '*' => Http::response('invalid_user_specified', 200),
        ]);

        $job = new RemoveClanMember(12345, 67890);

        $job->handle($this->makeForumService((object) [
            'usergroupid'    => '2',
            'membergroupids' => '',
        ]));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'do=remaod'));
    }
}
