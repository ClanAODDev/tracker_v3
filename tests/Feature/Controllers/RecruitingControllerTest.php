<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RecruitingControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_index_displays_recruit_page()
    {
        $officer = $this->createOfficer();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.initial'));

        $response->assertOk();
        $response->assertViewIs('recruit.index');
        $response->assertViewHas('divisions');
    }

    public function test_index_requires_authentication()
    {
        $response = $this->get(route('recruiting.initial'));

        $response->assertRedirect('/login');
    }

    public function test_member_cannot_access_recruitment()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser(['division_id' => $division->id]);
        $user->role = Role::MEMBER;
        $user->save();

        $response = $this->actingAs($user)
            ->get(route('recruiting.initial'));

        $response->assertForbidden();
    }

    public function test_form_displays_for_active_division()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.form', $division->slug));

        $response->assertOk();
        $response->assertViewIs('recruit.form');
        $response->assertViewHas('division');
    }

    public function test_form_redirects_for_shutdown_division()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $division->shutdown_at = now()->subDay();
        $division->save();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.form', $division->slug));

        $response->assertRedirect();
    }

    public function test_get_division_recruit_data_returns_json()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $this->createSquad($platoon);

        $response = $this->actingAs($officer)
            ->getJson(route('recruiting.divisionData', $division->slug));

        $response->assertOk();
        $response->assertJsonStructure([
            'platoons',
            'threads',
            'tasks',
            'welcome_area',
            'welcome_pm',
            'use_welcome_thread',
            'locality',
        ]);
    }

    public function test_submit_recruitment_creates_member()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $squad = $this->createSquad($platoon);

        $response = $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'member_id' => 99999,
                'forum_name' => 'TestRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
                'squad' => $squad->id,
            ]);

        $this->assertDatabaseHas('members', [
            'clan_id' => 99999,
            'name' => 'TestRecruit',
            'division_id' => $division->id,
        ]);
    }

    public function test_submit_recruitment_creates_transfer_record()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);

        $response = $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'member_id' => 88888,
                'forum_name' => 'TransferTestRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
            ]);

        $this->assertDatabaseHas('transfers', [
            'division_id' => $division->id,
        ]);
    }

    public function test_submit_recruitment_creates_rank_action()
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);

        $response = $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'member_id' => 77777,
                'forum_name' => 'RankTestRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
            ]);

        $this->assertDatabaseHas('rank_actions', [
            'rank' => Rank::RECRUIT->value,
            'justification' => 'New recruit',
        ]);
    }

    public function test_get_division_recruit_data_excludes_pending_discord_users(): void
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();

        User::factory()->pending()->create([
            'discord_username' => 'PendingUser1',
        ]);

        $response = $this->actingAs($officer)
            ->getJson(route('recruiting.divisionData', $division->slug));

        $response->assertOk();
        $response->assertJsonPath('pending_discord', []);
    }

    /**
     * @todo Enable when Discord recruitment is implemented
     */
    public function test_submit_discord_recruitment_creates_member(): void
    {
        $this->markTestSkipped('Discord recruitment is currently disabled');

        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $pendingUser = User::factory()->pending()->create();

        $response = $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'pending_user_id' => $pendingUser->id,
                'forum_name' => 'DiscordRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
                'ingame_name' => 'GameHandle',
            ]);

        $this->assertDatabaseHas('members', [
            'name' => 'DiscordRecruit',
            'division_id' => $division->id,
        ]);

        $pendingUser->refresh();
        $this->assertNotNull($pendingUser->member_id);
    }

    /**
     * @todo Enable when Discord recruitment is implemented
     */
    public function test_submit_discord_recruitment_returns_error_on_forum_creation_failure(): void
    {
        $this->markTestSkipped('Discord recruitment is currently disabled');

        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $pendingUser = User::factory()->pending()->create();

        $response = $this->actingAs($officer)
            ->postJson(route('recruiting.addMember'), [
                'division' => $division->slug,
                'pending_user_id' => $pendingUser->id,
                'forum_name' => 'TakenName',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
                'ingame_name' => 'GameHandle',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Forum account creation is not yet implemented.');
    }

    /**
     * @todo Enable when Discord recruitment is implemented
     */
    public function test_submit_discord_recruitment_rejects_invalid_pending_user(): void
    {
        $this->markTestSkipped('Discord recruitment is currently disabled');

        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);

        $response = $this->actingAs($officer)
            ->postJson(route('recruiting.addMember'), [
                'division' => $division->slug,
                'pending_user_id' => 99999,
                'forum_name' => 'TestRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
                'ingame_name' => 'GameHandle',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Pending Discord user not found.');
    }

    /**
     * @todo Enable when Discord recruitment is implemented
     */
    public function test_submit_discord_recruitment_links_user_to_member(): void
    {
        $this->markTestSkipped('Discord recruitment is currently disabled');

        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789',
            'discord_username' => 'TestDiscord',
        ]);

        $response = $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'pending_user_id' => $pendingUser->id,
                'forum_name' => 'LinkedRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
                'ingame_name' => 'GameHandle',
            ]);

        $pendingUser->refresh();
        $this->assertNotNull($pendingUser->member_id);
        $this->assertFalse($pendingUser->isPendingRegistration());
    }
}
