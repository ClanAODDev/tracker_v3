<?php

namespace Tests\Unit\Jobs;

use App\Jobs\AddClanMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AddClanMemberTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['aod.token' => 'test-token']);
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
        $job->handle();

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
        $job->handle();

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
        $job->handle();
    }
}
