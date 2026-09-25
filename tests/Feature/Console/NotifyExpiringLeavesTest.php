<?php

namespace Tests\Feature\Console;

use App\Models\Division;
use App\Models\Leave;
use App\Models\Member;
use App\Models\Note;
use App\Models\User;
use App\Notifications\Channel\NotifyDivisionLoaExpired;
use App\Notifications\Channel\NotifyDivisionLoaExpiring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class NotifyExpiringLeavesTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /**
     * Leave::factory()/Note::factory() eagerly create their own throwaway
     * member (and division) inside their definitions regardless of any
     * overrides passed to create(), unlike the rest of this app's factories
     * which use lazy factory references. Building the row directly avoids
     * that noise, which otherwise piles up fast across many test cases.
     */
    private function createLeave(Member $member, $endDate, bool $approved = true): Leave
    {
        $author = User::factory()->create();
        $note   = Note::create([
            'body'      => 'Leave of absence',
            'member_id' => $member->id,
            'author_id' => $author->id,
            'type'      => 'misc',
        ]);

        return Leave::create([
            'member_id'    => $member->id,
            'requester_id' => $author->id,
            'approver_id'  => $approved ? $author->id : null,
            'reason'       => 'other',
            'note_id'      => $note->id,
            'end_date'     => $endDate,
        ]);
    }

    #[Test]
    public function command_exits_successfully_with_no_active_divisions(): void
    {
        Division::factory()->inactive()->create();

        $this->artisan('tracker:notify-expiring-loas')
            ->assertSuccessful()
            ->expectsOutput('No active divisions found.');

        Notification::assertNothingSent();
    }

    #[Test]
    public function command_sends_no_notification_when_no_leaves_are_ending(): void
    {
        $division = $this->createActiveDivision();
        $member   = Member::factory()->create(['division_id' => $division->id]);
        $this->createLeave($member, now()->addWeeks(2));

        $this->artisan('tracker:notify-expiring-loas')
            ->assertSuccessful()
            ->expectsOutputToContain('Expiring flagged: 0')
            ->expectsOutputToContain('Expired flagged: 0');

        Notification::assertNothingSent();
    }

    #[Test]
    public function command_notifies_when_a_leave_expires_in_three_days(): void
    {
        $division = $this->createActiveDivision();
        $member   = Member::factory()->create(['division_id' => $division->id]);
        $this->createLeave($member, today()->addDays(3));

        $this->artisan('tracker:notify-expiring-loas')
            ->assertSuccessful()
            ->expectsOutputToContain('Expiring flagged: 1');

        Notification::assertSentTo($division, NotifyDivisionLoaExpiring::class);
        Notification::assertNotSentTo($division, NotifyDivisionLoaExpired::class);
    }

    #[Test]
    public function command_notifies_when_a_leave_expired_yesterday(): void
    {
        $division = $this->createActiveDivision();
        $member   = Member::factory()->create(['division_id' => $division->id]);
        $this->createLeave($member, today()->subDay());

        $this->artisan('tracker:notify-expiring-loas')
            ->assertSuccessful()
            ->expectsOutputToContain('Expired flagged: 1');

        Notification::assertSentTo($division, NotifyDivisionLoaExpired::class);
        Notification::assertNotSentTo($division, NotifyDivisionLoaExpiring::class);
    }

    #[Test]
    public function leaves_outside_the_exact_thresholds_are_ignored(): void
    {
        $division = $this->createActiveDivision();
        $memberA  = Member::factory()->create(['division_id' => $division->id]);
        $memberB  = Member::factory()->create(['division_id' => $division->id]);
        $memberC  = Member::factory()->create(['division_id' => $division->id]);
        $this->createLeave($memberA, today()->addDays(2));
        $this->createLeave($memberB, today()->addDays(4));
        $this->createLeave($memberC, today()->subDays(2));

        $this->artisan('tracker:notify-expiring-loas')
            ->assertSuccessful()
            ->expectsOutputToContain('Expiring flagged: 0')
            ->expectsOutputToContain('Expired flagged: 0');

        Notification::assertNothingSent();
    }

    #[Test]
    public function unapproved_leaves_are_not_flagged(): void
    {
        $division = $this->createActiveDivision();
        $member   = Member::factory()->create(['division_id' => $division->id]);
        $this->createLeave($member, today()->addDays(3), approved: false);

        $this->artisan('tracker:notify-expiring-loas')
            ->assertSuccessful()
            ->expectsOutputToContain('Expiring flagged: 0');

        Notification::assertNothingSent();
    }

    #[Test]
    public function multiple_expiring_leaves_in_the_same_division_send_a_single_notification(): void
    {
        $division = $this->createActiveDivision();
        $memberA  = Member::factory()->create(['division_id' => $division->id]);
        $memberB  = Member::factory()->create(['division_id' => $division->id]);
        $this->createLeave($memberA, today()->addDays(3));
        $this->createLeave($memberB, today()->addDays(3));

        $this->artisan('tracker:notify-expiring-loas')
            ->assertSuccessful()
            ->expectsOutputToContain('Expiring flagged: 2');

        Notification::assertSentToOnce($division, NotifyDivisionLoaExpiring::class);
    }

    #[Test]
    public function separate_divisions_get_separate_notifications(): void
    {
        $divisionA = $this->createActiveDivision();
        $divisionB = $this->createActiveDivision();
        $memberA   = Member::factory()->create(['division_id' => $divisionA->id]);
        $memberB   = Member::factory()->create(['division_id' => $divisionB->id]);
        $this->createLeave($memberA, today()->addDays(3));
        $this->createLeave($memberB, today()->addDays(3));

        $this->artisan('tracker:notify-expiring-loas')
            ->assertSuccessful()
            ->expectsOutputToContain('Divisions notified: 2');

        Notification::assertSentTo($divisionA, NotifyDivisionLoaExpiring::class);
        Notification::assertSentTo($divisionB, NotifyDivisionLoaExpiring::class);
    }

    #[Test]
    public function dry_run_outputs_leaves_without_sending_notifications(): void
    {
        $division = $this->createActiveDivision(['name' => 'Alpha']);
        $member   = Member::factory()->create(['division_id' => $division->id, 'name' => 'OnLeaveMember']);
        $this->createLeave($member, today()->addDays(3));

        $this->artisan('tracker:notify-expiring-loas --dry-run')
            ->assertSuccessful()
            ->expectsOutputToContain('[Alpha]')
            ->expectsOutputToContain('OnLeaveMember');

        Notification::assertNothingSent();
    }

    #[Test]
    public function division_option_limits_command_to_a_single_division(): void
    {
        $target  = $this->createActiveDivision(['name' => 'Target Division']);
        $other   = $this->createActiveDivision();
        $memberA = Member::factory()->create(['division_id' => $target->id]);
        $memberB = Member::factory()->create(['division_id' => $other->id]);
        $this->createLeave($memberA, today()->addDays(3));
        $this->createLeave($memberB, today()->addDays(3));

        $this->artisan('tracker:notify-expiring-loas', ['--division' => 'target-division'])
            ->assertSuccessful()
            ->expectsOutputToContain('Expiring flagged: 1');

        Notification::assertSentTo($target, NotifyDivisionLoaExpiring::class);
        Notification::assertNotSentTo($other, NotifyDivisionLoaExpiring::class);
    }

    #[Test]
    public function inactive_divisions_are_skipped(): void
    {
        $inactive = Division::factory()->inactive()->create();
        $member   = Member::factory()->create(['division_id' => $inactive->id]);
        $this->createLeave($member, today()->addDays(3));

        $this->artisan('tracker:notify-expiring-loas')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
