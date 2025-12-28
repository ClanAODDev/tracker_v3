<?php

namespace Tests\Feature\Controllers;

use App\Models\ActivityReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ActivityReminderTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_officer_can_set_activity_reminder()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($officer)
            ->postJson(route('member.set-activity-reminder', $member->clan_id));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('activity_reminders', [
            'member_id' => $member->clan_id,
            'division_id' => $division->id,
            'reminded_by_id' => $officer->id,
        ]);

        $member->refresh();
        $this->assertNotNull($member->last_activity_reminder_at);
        $this->assertEquals($officer->id, $member->activity_reminded_by_id);
    }

    public function test_cannot_remind_yourself()
    {
        $officer = $this->createOfficer();

        $response = $this->actingAs($officer)
            ->postJson(route('member.set-activity-reminder', $officer->member->clan_id));

        $response->assertForbidden();
        $response->assertJson(['message' => 'Cannot remind yourself']);
    }

    public function test_cannot_remind_same_member_twice_in_one_day()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        ActivityReminder::create([
            'member_id' => $member->clan_id,
            'division_id' => $division->id,
            'reminded_by_id' => $officer->id,
        ]);

        $response = $this->actingAs($officer)
            ->postJson(route('member.set-activity-reminder', $member->clan_id));

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Already reminded today']);
    }

    public function test_sr_ldr_can_clear_activity_reminders()
    {
        $srLdr = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        ActivityReminder::create([
            'member_id' => $member->clan_id,
            'division_id' => $division->id,
            'reminded_by_id' => $srLdr->id,
        ]);
        ActivityReminder::create([
            'member_id' => $member->clan_id,
            'division_id' => $division->id,
            'reminded_by_id' => $srLdr->id,
            'created_at' => now()->subDays(7),
        ]);

        $member->update([
            'last_activity_reminder_at' => now(),
            'activity_reminded_by_id' => $srLdr->id,
        ]);

        $response = $this->actingAs($srLdr)
            ->deleteJson(route('member.clear-activity-reminders', $member->clan_id));

        $response->assertOk();
        $response->assertJson(['success' => true, 'count' => 2]);

        $this->assertDatabaseMissing('activity_reminders', [
            'member_id' => $member->clan_id,
        ]);

        $member->refresh();
        $this->assertNull($member->last_activity_reminder_at);
        $this->assertNull($member->activity_reminded_by_id);
    }

    public function test_officer_cannot_clear_activity_reminders()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member = $this->createMember(['division_id' => $division->id]);

        ActivityReminder::create([
            'member_id' => $member->clan_id,
            'division_id' => $division->id,
            'reminded_by_id' => $officer->id,
        ]);

        $response = $this->actingAs($officer)
            ->deleteJson(route('member.clear-activity-reminders', $member->clan_id));

        $response->assertForbidden();
    }

    public function test_cannot_clear_own_activity_reminders()
    {
        $srLdr = $this->createSeniorLeader();

        ActivityReminder::create([
            'member_id' => $srLdr->member->clan_id,
            'division_id' => $srLdr->member->division_id,
            'reminded_by_id' => $srLdr->id,
        ]);

        $response = $this->actingAs($srLdr)
            ->deleteJson(route('member.clear-activity-reminders', $srLdr->member->clan_id));

        $response->assertForbidden();
        $response->assertJson(['message' => 'Cannot clear your own reminders']);
    }

    public function test_bulk_reminder_creates_reminders_for_multiple_members()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member1 = $this->createMember(['division_id' => $division->id]);
        $member2 = $this->createMember(['division_id' => $division->id]);
        $member3 = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($officer)
            ->post(route('bulk-reminder.store', $division->slug), [
                'member_ids' => implode(',', [$member1->clan_id, $member2->clan_id, $member3->clan_id]),
                'confirm' => true,
                'redirect' => route('division.inactive-members', $division->slug),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activity_reminders', ['member_id' => $member1->clan_id]);
        $this->assertDatabaseHas('activity_reminders', ['member_id' => $member2->clan_id]);
        $this->assertDatabaseHas('activity_reminders', ['member_id' => $member3->clan_id]);
    }

    public function test_bulk_reminder_skips_members_already_reminded_today()
    {
        $officer = $this->createOfficer();
        $division = $officer->member->division;
        $member1 = $this->createMember(['division_id' => $division->id]);
        $member2 = $this->createMember(['division_id' => $division->id]);

        ActivityReminder::create([
            'member_id' => $member1->clan_id,
            'division_id' => $division->id,
            'reminded_by_id' => $officer->id,
        ]);

        $response = $this->actingAs($officer)
            ->post(route('bulk-reminder.store', $division->slug), [
                'member_ids' => implode(',', [$member1->clan_id, $member2->clan_id]),
                'confirm' => true,
                'redirect' => route('division.inactive-members', $division->slug),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('reminder_result.count', 1);
        $response->assertSessionHas('reminder_result.skipped', 1);
    }

    public function test_unauthenticated_user_cannot_set_reminder()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $division->id]);

        $response = $this->postJson(route('member.set-activity-reminder', $member->clan_id));

        $response->assertUnauthorized();
    }
}
