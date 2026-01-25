<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Enums\Role;
use App\Models\User;
use App\Services\AODForumService;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery;
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

    protected function mockForumUserCreation(int $clanId): void
    {
        $mock = Mockery::mock('alias:' . AODForumService::class);
        $mock->shouldReceive('createForumUser')
            ->andReturn(['success' => true, 'clan_id' => $clanId]);

        $forumUser = (object) ['userid' => $clanId];
        $procedureMock = Mockery::mock(ForumProcedureService::class);
        $procedureMock->shouldReceive('checkUser')->andReturn($forumUser);
        $this->app->instance(ForumProcedureService::class, $procedureMock);
    }

    protected function mockForumUserCreationFailure(string $error): void
    {
        $mock = Mockery::mock('alias:' . AODForumService::class);
        $mock->shouldReceive('createForumUser')
            ->andReturn(['success' => false, 'error' => $error]);
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

    public function test_get_division_recruit_data_excludes_pending_users_without_dob(): void
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();

        User::factory()->pending()->create([
            'discord_username' => 'PendingUser1',
            'date_of_birth' => null,
        ]);

        $response = $this->actingAs($officer)
            ->getJson(route('recruiting.divisionData', $division->slug));

        $response->assertOk();
        $response->assertJsonPath('pending_discord', []);
    }

    public function test_get_division_recruit_data_includes_pending_users_with_dob(): void
    {
        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();

        $pendingUser = User::factory()->pending()->create([
            'discord_username' => 'ReadyUser',
        ]);

        $response = $this->actingAs($officer)
            ->getJson(route('recruiting.divisionData', $division->slug));

        $response->assertOk();
        $response->assertJsonCount(1, 'pending_discord');
        $response->assertJsonPath('pending_discord.0.discord_username', 'ReadyUser');
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_submit_discord_recruitment_creates_member(): void
    {
        $this->mockForumUserCreation(12345);

        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $pendingUser = User::factory()->pending()->create();

        $this->actingAs($officer)
            ->post(route('recruiting.addMember'), [
                'division' => $division->slug,
                'pending_user_id' => $pendingUser->id,
                'forum_name' => 'DiscordRecruit',
                'rank' => Rank::RECRUIT->value,
                'platoon' => $platoon->id,
                'ingame_name' => 'GameHandle',
            ]);

        $this->assertDatabaseHas('members', [
            'clan_id' => 12345,
            'name' => 'DiscordRecruit',
            'division_id' => $division->id,
        ]);

        $pendingUser->refresh();
        $this->assertNotNull($pendingUser->member_id);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_submit_discord_recruitment_returns_error_on_forum_creation_failure(): void
    {
        $this->mockForumUserCreationFailure('Username already taken');

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
        $response->assertJsonPath('message', 'Username already taken');
    }

    public function test_submit_discord_recruitment_rejects_invalid_pending_user(): void
    {
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

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_submit_discord_recruitment_links_user_to_member(): void
    {
        $this->mockForumUserCreation(67890);

        $officer = $this->createOfficer();
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789',
            'discord_username' => 'TestDiscord',
        ]);

        $this->actingAs($officer)
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
