<?php

namespace Tests\Feature;

use App\Data\TenureReconciliation;
use App\Jobs\CleanupTenureAwards;
use App\Models\Award;
use App\Models\Member;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwardsBulk;
use App\Notifications\DM\NotifyMemberAwardReceived;
use App\Services\TenureAwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TenureAwardServiceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (TenureAwardService::AWARD_IDS as $years => $id) {
            Award::factory()->create(['id' => $id, 'name' => "{$years} Years of Service", 'division_id' => null]);
        }

        $this->travelTo(now()->setDate(2026, 6, 15)->setTime(12, 0));
    }

    private function memberJoined(string $date): Member
    {
        return $this->createMember([
            'division_id' => $this->createActiveDivision()->id,
            'join_date'   => $date,
        ]);
    }

    private function awardIdsFor(Member $member): array
    {
        return MemberAward::where('member_id', $member->id)->pluck('award_id')->sort()->values()->all();
    }

    private function reconcile(Member $member, bool $persist = true, bool $removeInvalid = true): TenureReconciliation
    {
        return app(TenureAwardService::class)->reconcile(
            Member::query()->whereKey($member->id),
            $persist,
            $removeInvalid,
        );
    }

    #[Test]
    public function it_grants_every_tier_the_member_has_reached(): void
    {
        $member = $this->memberJoined('2015-01-01');

        $this->reconcile($member);

        $this->assertSame([18, 19], $this->awardIdsFor($member));
    }

    #[Test]
    public function it_does_not_grant_early_in_the_anniversary_month(): void
    {
        $member = $this->memberJoined('2021-06-20');

        $this->reconcile($member);

        $this->assertSame([], $this->awardIdsFor($member));
    }

    #[Test]
    public function an_anniversary_day_missing_from_the_month_is_granted_on_the_last_day(): void
    {
        $this->travelTo(now()->setDate(2026, 2, 28)->setTime(17, 0));
        $member = $this->memberJoined('2021-02-28');
        $member->update(['join_date' => '2020-02-29']);

        $this->reconcile($member);

        $this->assertSame([18], $this->awardIdsFor($member));
    }

    #[Test]
    public function it_grants_on_the_anniversary_day(): void
    {
        $member = $this->memberJoined('2021-06-15');

        $this->reconcile($member);

        $this->assertSame([18], $this->awardIdsFor($member));
    }

    #[Test]
    public function holding_a_higher_tier_grants_the_lower_tiers(): void
    {
        $member = $this->memberJoined('2025-01-01');
        MemberAward::create(['member_id' => $member->id, 'award_id' => 19, 'approved' => true]);

        $this->reconcile($member);

        $this->assertSame([18, 19], $this->awardIdsFor($member));
    }

    #[Test]
    public function held_awards_are_not_removed_in_grant_only_mode(): void
    {
        $member = $this->memberJoined('2025-01-01');
        MemberAward::create(['member_id' => $member->id, 'award_id' => 139, 'approved' => true]);

        $stats = $this->reconcile($member, removeInvalid: false);

        $this->assertContains(139, $this->awardIdsFor($member));
        $this->assertSame(0, $stats->invalid);
    }

    #[Test]
    public function a_held_tier_below_one_the_member_holds_is_never_invalid(): void
    {
        $member = $this->memberJoined('2025-01-01');
        MemberAward::create(['member_id' => $member->id, 'award_id' => 18, 'approved' => true]);
        MemberAward::create(['member_id' => $member->id, 'award_id' => 19, 'approved' => true]);

        $stats = $this->reconcile($member);

        $this->assertSame(0, $stats->invalid);
        $this->assertSame([18, 19], $this->awardIdsFor($member));
    }

    #[Test]
    public function a_recent_member_with_no_awards_is_left_alone(): void
    {
        $member = $this->memberJoined('2025-01-01');

        $stats = $this->reconcile($member);

        $this->assertSame(0, $stats->missing);
        $this->assertSame([], $this->awardIdsFor($member));
    }

    #[Test]
    public function dry_run_changes_nothing(): void
    {
        $member = $this->memberJoined('2015-01-01');

        $stats = $this->reconcile($member, persist: false);

        $this->assertSame(2, $stats->missing);
        $this->assertSame([], $this->awardIdsFor($member));
    }

    #[Test]
    public function members_without_a_join_date_are_skipped(): void
    {
        $member = $this->memberJoined('2015-01-01');
        $member->forceFill(['join_date' => null])->saveQuietly();

        $stats = $this->reconcile($member);

        $this->assertSame(0, $stats->missing);
    }

    #[Test]
    public function running_twice_grants_once(): void
    {
        $member = $this->memberJoined('2015-01-01');

        $this->reconcile($member);
        $second = $this->reconcile($member);

        $this->assertSame(0, $second->missing);
        $this->assertCount(2, $this->awardIdsFor($member));
    }

    #[Test]
    public function the_job_notifies_only_for_the_highest_tier_granted(): void
    {
        Notification::fake();

        $member = $this->memberJoined('2015-01-01');
        $member->division->settings()->set('chat_alerts.member_awarded', 'officers');

        (new CleanupTenureAwards)->handle(app(TenureAwardService::class));

        Notification::assertSentToTimes($member, NotifyMemberAwardReceived::class, 1);
        Notification::assertSentToTimes($member->division, NotifyDivisionMemberAwardsBulk::class, 1);
        Notification::assertSentTo(
            $member,
            NotifyMemberAwardReceived::class,
            fn (NotifyMemberAwardReceived $notification) => (new ReflectionProperty($notification, 'award'))->getValue($notification) === '10 Years of Service',
        );
    }

    #[Test]
    public function the_job_sends_one_division_message_for_many_members(): void
    {
        Notification::fake();

        $division = $this->createActiveDivision();
        $division->settings()->set('chat_alerts.member_awarded', 'officers');

        $members = collect(['2015-01-01', '2021-06-01', '2010-02-02'])->map(
            fn (string $date) => $this->createMember(['division_id' => $division->id, 'join_date' => $date]),
        );

        (new CleanupTenureAwards)->handle(app(TenureAwardService::class));

        Notification::assertSentToTimes($division, NotifyDivisionMemberAwardsBulk::class, 1);
        $members->each(fn (Member $member) => Notification::assertSentToTimes($member, NotifyMemberAwardReceived::class, 1));
    }

    #[Test]
    public function the_job_grants_without_notifying_in_silent_mode(): void
    {
        Notification::fake();

        $member = $this->memberJoined('2015-01-01');
        $member->division->settings()->set('chat_alerts.member_awarded', 'officers');

        (new CleanupTenureAwards(silent: true))->handle(app(TenureAwardService::class));

        $this->assertSame([18, 19], $this->awardIdsFor($member));
        Notification::assertNothingSent();
    }

    #[Test]
    public function the_command_supports_silent_and_dry_run(): void
    {
        Notification::fake();

        $member = $this->memberJoined('2015-01-01');

        $this->artisan('tracker:cleanup-tenure-awards', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame([], $this->awardIdsFor($member));

        $this->artisan('tracker:cleanup-tenure-awards', ['--silent' => true])->assertSuccessful();
        $this->assertSame([18, 19], $this->awardIdsFor($member));
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_grants_the_twenty_five_and_thirty_year_tiers(): void
    {
        $member = $this->memberJoined('1995-01-01');

        $this->reconcile($member);

        $this->assertSame([18, 19, 139, 140, 343, 344], $this->awardIdsFor($member));
    }

    #[Test]
    public function the_job_sends_nothing_when_there_is_nothing_to_grant(): void
    {
        Notification::fake();

        $this->memberJoined('2025-01-01');

        (new CleanupTenureAwards)->handle(app(TenureAwardService::class));

        Notification::assertNothingSent();
    }

    #[Test]
    public function the_job_skips_the_division_alert_when_not_configured(): void
    {
        Notification::fake();

        $member = $this->memberJoined('2015-01-01');

        (new CleanupTenureAwards)->handle(app(TenureAwardService::class));

        Notification::assertSentTo($member, NotifyMemberAwardReceived::class);
        Notification::assertNotSentTo($member->division, NotifyDivisionMemberAwardsBulk::class);
    }

    #[Test]
    public function the_scheduled_task_is_registered_disabled_until_an_admin_enables_it(): void
    {
        $this->assertDatabaseHas('monitored_scheduled_tasks', [
            'name'       => 'cleanup-tenure-awards',
            'is_enabled' => false,
        ]);
    }
}
