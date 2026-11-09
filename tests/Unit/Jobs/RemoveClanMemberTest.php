<?php

namespace Tests\Unit\Jobs;

use App\Jobs\RemoveClanMember;
use App\Models\Division;
use App\Models\Member;
use App\Notifications\Channel\NotifyDivisionForumRemovalFailed;
use App\Notifications\DM\NotifyRemoverForumRemovalFailed;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\ManuallyFailedException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
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

    #[Test]
    public function job_fails_without_retrying_when_the_member_name_is_not_forum_safe()
    {
        Http::fake();

        $member = Member::factory()->create(['name' => 'Bad<Name']);

        $job = (new RemoveClanMember($member->clan_id, 67890))->withFakeQueueInteractions();
        $job->handle($this->makeForumService());

        $job->assertFailed();
        Http::assertNothingSent();
    }

    #[Test]
    public function the_remover_is_messaged_when_the_division_is_unknown()
    {
        Notification::fake();

        $remover = Member::factory()->create(['discord_id' => '123456789012345678']);
        $member  = Member::factory()->create(['name' => 'Removed']);

        (new RemoveClanMember($member->clan_id, $remover->clan_id))
            ->failed(new ManuallyFailedException('Forum rejected the removal'));

        Notification::assertSentTo($remover, NotifyRemoverForumRemovalFailed::class);
    }

    #[Test]
    public function the_division_officers_are_alerted_when_the_job_fails()
    {
        Notification::fake();

        $division = Division::factory()->create();
        $remover  = Member::factory()->create(['discord_id' => '123456789012345678']);
        $member   = Member::factory()->create(['name' => 'Removed']);

        (new RemoveClanMember($member->clan_id, $remover->clan_id, $division->id))
            ->failed(new ManuallyFailedException('Forum rejected the removal'));

        Notification::assertSentTo($division, NotifyDivisionForumRemovalFailed::class);
        Notification::assertNotSentTo($remover, NotifyRemoverForumRemovalFailed::class);
    }
}
