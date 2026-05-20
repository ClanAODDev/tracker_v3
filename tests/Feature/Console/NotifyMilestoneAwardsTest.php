<?php

namespace Tests\Feature\Console;

use App\Models\Award;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyMilestoneAwardReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class NotifyMilestoneAwardsTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    #[Test]
    public function command_exits_successfully_with_no_active_divisions(): void
    {
        Division::factory()->inactive()->create();

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutput('No active divisions found.');

        Notification::assertNothingSent();
    }

    #[Test]
    public function command_sends_no_notification_when_no_members_have_anniversaries_this_month(): void
    {
        $division = $this->createActiveDivision();

        Member::factory()->create([
            'division_id' => $division->id,
            'join_date'   => now()->subYears(3)->startOfMonth(),
        ]);

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutputToContain('Members flagged: 0');

        Notification::assertNothingSent();
    }

    #[Test]
    public function command_sends_no_notification_when_milestone_member_already_has_award(): void
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->global()->create(['name' => '5 Years of Service']);
        $member   = Member::factory()->create([
            'division_id' => $division->id,
            'join_date'   => now()->subYears(5)->startOfMonth(),
        ]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member->id,
            'award_id'  => $award->id,
        ]);

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutputToContain('Members flagged: 0');

        Notification::assertNothingSent();
    }

    #[Test]
    public function command_notifies_division_when_milestone_member_is_missing_award(): void
    {
        $division = $this->createActiveDivision();
        Award::factory()->global()->create(['name' => '5 Years of Service']);

        Member::factory()->create([
            'division_id' => $division->id,
            'join_date'   => now()->subYears(5)->startOfMonth(),
        ]);

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutputToContain('Members flagged: 1')
            ->expectsOutputToContain('Divisions notified: 1');

        Notification::assertSentTo($division, NotifyMilestoneAwardReminder::class);
    }

    #[Test]
    public function command_sends_single_notification_per_division_for_multiple_missing_members(): void
    {
        $division = $this->createActiveDivision();
        Award::factory()->global()->create(['name' => '5 Years of Service']);
        Award::factory()->global()->create(['name' => '10 Years of Service']);

        Member::factory()->create([
            'division_id' => $division->id,
            'join_date'   => now()->subYears(5)->startOfMonth(),
        ]);
        Member::factory()->create([
            'division_id' => $division->id,
            'join_date'   => now()->subYears(10)->startOfMonth(),
        ]);

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutputToContain('Members flagged: 2')
            ->expectsOutputToContain('Divisions notified: 1');

        Notification::assertSentToTimes($division, NotifyMilestoneAwardReminder::class, 1);
    }

    #[Test]
    public function command_sends_separate_notifications_per_division(): void
    {
        $divisionA = $this->createActiveDivision();
        $divisionB = $this->createActiveDivision();

        Award::factory()->global()->create(['name' => '10 Years of Service']);

        Member::factory()->create([
            'division_id' => $divisionA->id,
            'join_date'   => now()->subYears(10)->startOfMonth(),
        ]);
        Member::factory()->create([
            'division_id' => $divisionB->id,
            'join_date'   => now()->subYears(10)->startOfMonth(),
        ]);

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutputToContain('Members flagged: 2')
            ->expectsOutputToContain('Divisions notified: 2');

        Notification::assertSentTo($divisionA, NotifyMilestoneAwardReminder::class);
        Notification::assertSentTo($divisionB, NotifyMilestoneAwardReminder::class);
    }

    #[Test]
    public function command_notifies_only_divisions_with_missing_awards(): void
    {
        $divisionWithMissing    = $this->createActiveDivision();
        $divisionWithoutMissing = $this->createActiveDivision();

        $award = Award::factory()->global()->create(['name' => '5 Years of Service']);

        $memberWithAward = Member::factory()->create([
            'division_id' => $divisionWithoutMissing->id,
            'join_date'   => now()->subYears(5)->startOfMonth(),
        ]);

        MemberAward::factory()->approved()->create([
            'member_id' => $memberWithAward->id,
            'award_id'  => $award->id,
        ]);

        Member::factory()->create([
            'division_id' => $divisionWithMissing->id,
            'join_date'   => now()->subYears(5)->startOfMonth(),
        ]);

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutputToContain('Members flagged: 1')
            ->expectsOutputToContain('Divisions notified: 1');

        Notification::assertSentTo($divisionWithMissing, NotifyMilestoneAwardReminder::class);
        Notification::assertNotSentTo($divisionWithoutMissing, NotifyMilestoneAwardReminder::class);
    }

    #[Test]
    public function dry_run_outputs_members_without_sending_notifications(): void
    {
        $division = $this->createActiveDivision(['name' => 'Alpha']);
        Award::factory()->global()->create(['name' => '15 Years of Service']);

        Member::factory()->create([
            'name'        => 'LongServingMember',
            'division_id' => $division->id,
            'join_date'   => now()->subYears(15)->startOfMonth(),
        ]);

        $this->artisan('tracker:notify-milestone-awards --dry-run')
            ->assertSuccessful()
            ->expectsOutputToContain('[Alpha]')
            ->expectsOutputToContain('LongServingMember')
            ->expectsOutputToContain('[Dry Run]');

        Notification::assertNothingSent();
    }

    #[Test]
    public function inactive_divisions_are_skipped(): void
    {
        $inactive = Division::factory()->inactive()->create();
        Award::factory()->global()->create(['name' => '5 Years of Service']);

        Member::factory()->create([
            'division_id' => $inactive->id,
            'join_date'   => now()->subYears(5)->startOfMonth(),
        ]);

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutput('No active divisions found.');

        Notification::assertNothingSent();
    }

    #[Test]
    public function command_skips_members_with_anniversaries_in_other_months(): void
    {
        $division   = $this->createActiveDivision();
        $otherMonth = now()->month === 1 ? 2 : 1;

        Award::factory()->global()->create(['name' => '5 Years of Service']);

        Member::factory()->create([
            'division_id' => $division->id,
            'join_date'   => now()->subYears(5)->month($otherMonth)->startOfMonth(),
        ]);

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutputToContain('Members flagged: 0');

        Notification::assertNothingSent();
    }

    #[Test]
    public function command_supports_all_milestone_years(): void
    {
        $division = $this->createActiveDivision();

        foreach ([5, 10, 15, 20] as $years) {
            Award::factory()->global()->create(['name' => "{$years} Years of Service"]);
            Member::factory()->create([
                'division_id' => $division->id,
                'join_date'   => now()->subYears($years)->startOfMonth(),
            ]);
        }

        $this->artisan('tracker:notify-milestone-awards')
            ->assertSuccessful()
            ->expectsOutputToContain('Members flagged: 4');

        Notification::assertSentToTimes($division, NotifyMilestoneAwardReminder::class, 1);
    }
}
