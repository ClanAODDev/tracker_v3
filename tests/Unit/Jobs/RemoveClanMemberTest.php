<?php

namespace Tests\Unit\Jobs;

use App\Jobs\RemoveClanMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RemoveClanMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['aod.token' => 'test-token']);
    }

    public function test_job_can_be_instantiated()
    {
        $job = new RemoveClanMember(12345, 67890);

        $this->assertInstanceOf(RemoveClanMember::class, $job);
    }

    public function test_job_calls_forum_service_with_correct_parameters()
    {
        Http::fake([
            '*' => Http::response('saved_user_x_successfully', 200),
        ]);

        $memberIdBeingRemoved = 12345;
        $impersonatingMemberId = 67890;

        $job = new RemoveClanMember($memberIdBeingRemoved, $impersonatingMemberId);
        $job->handle();

        Http::assertSent(function ($request) use ($memberIdBeingRemoved, $impersonatingMemberId) {
            $url = $request->url();

            return str_contains($url, "aod_userid={$impersonatingMemberId}")
                && str_contains($url, "u={$memberIdBeingRemoved}")
                && str_contains($url, 'do=remaod');
        });
    }

    public function test_job_throws_exception_on_failure()
    {
        Http::fake([
            '*' => Http::response('error_user_not_found', 200),
        ]);

        $job = new RemoveClanMember(12345, 67890);

        $this->expectException(\RuntimeException::class);
        $job->handle();
    }
}
