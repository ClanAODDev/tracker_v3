<?php

namespace Tests\Feature\Controllers;

use App\Enums\Role;
use App\Models\DivisionApplication;
use App\Models\User;
use App\Services\AODForumService;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RecruitingDiscordConfirmTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(AODForumService::class, function ($mock) {
            $mock->shouldReceive('getUserByEmail')->andReturn(null);
        });
    }

    private function confirm(User $actor, string $discordId)
    {
        return $this->actingAs($actor)->get(route('recruiting.discordConfirm', $discordId));
    }

    #[Test]
    public function shows_matching_pending_registration(): void
    {
        $pendingUser = User::factory()->pending()->create([
            'discord_id'       => '123456789012345678',
            'discord_username' => 'ReadyUser',
            'email'            => 'readyuser@example.com',
        ]);

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('recruit/discord-confirm')
                ->where('pendingUser.discord_username', 'ReadyUser')
                ->where('pendingUser.obfuscated_email', '***er@example.com'));
    }

    #[Test]
    public function targets_the_division_from_the_pending_application(): void
    {
        $division    = $this->createActiveDivision();
        $pendingUser = User::factory()->pending()->create(['discord_id' => '123456789012345678']);

        DivisionApplication::factory()->create([
            'user_id'     => $pendingUser->id,
            'division_id' => $division->id,
        ]);

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('targetDivision.name', $division->name)
                ->where(
                    'targetDivision.recruitFormUrl',
                    route('recruiting.form', $division) . '?pending_user_id=' . $pendingUser->id,
                ));
    }

    #[Test]
    public function shows_division_picker_when_no_application_on_file(): void
    {
        $this->createActiveDivision(['name' => 'Alpha Division']);
        $this->createActiveDivision(['name' => 'Bravo Division']);
        $pendingUser = User::factory()->pending()->create(['discord_id' => '123456789012345678']);

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('targetDivision', null)
                ->where('divisions', fn ($divisions) => collect($divisions)->pluck('name')
                    ->contains('Alpha Division')));
    }

    #[Test]
    public function redirects_when_applied_division_is_shutdown(): void
    {
        $division    = $this->createActiveDivision(['shutdown_at' => now()->subDay()]);
        $pendingUser = User::factory()->pending()->create(['discord_id' => '123456789012345678']);

        DivisionApplication::factory()->create([
            'user_id'     => $pendingUser->id,
            'division_id' => $division->id,
        ]);

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)->assertRedirect();
    }

    #[Test]
    public function shows_forum_account_when_found_and_eligible(): void
    {
        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789012345678',
            'email'      => 'recruit@example.com',
        ]);

        $this->mock(AODForumService::class, function ($mock) {
            $mock->shouldReceive('getUserByEmail')
                ->andReturn((object) ['userid' => 555, 'username' => 'ForumUser555']);
        });
        $this->mock(ForumProcedureService::class, function ($mock) {
            $mock->shouldReceive('getUser')
                ->andReturn((object) ['usergroupid' => 2, 'username' => 'ForumUser555']);
        });

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('forumAccount.found', true)
                ->where('forumAccount.eligible', true)
                ->where('forumAccount.username', 'ForumUser555'));
    }

    #[Test]
    public function shows_ineligible_reason_when_forum_account_blocked(): void
    {
        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789012345678',
            'email'      => 'recruit@example.com',
        ]);

        $this->mock(AODForumService::class, function ($mock) {
            $mock->shouldReceive('getUserByEmail')
                ->andReturn((object) ['userid' => 555, 'username' => 'BannedUser']);
        });
        $this->mock(ForumProcedureService::class, function ($mock) {
            $mock->shouldReceive('getUser')
                ->andReturn((object) ['usergroupid' => 49, 'username' => 'BannedUser']);
        });

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('forumAccount.eligible', false)
                ->where('forumAccount.rejection_reason', 'User forum account is banned'));
    }

    #[Test]
    public function shows_no_forum_account_message_when_not_found(): void
    {
        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789012345678',
            'email'      => 'recruit@example.com',
        ]);

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('forumAccount.found', false));
    }

    #[Test]
    public function shows_application_responses_when_present(): void
    {
        $division    = $this->createActiveDivision();
        $pendingUser = User::factory()->pending()->create(['discord_id' => '123456789012345678']);

        DivisionApplication::factory()->create([
            'user_id'     => $pendingUser->id,
            'division_id' => $division->id,
            'responses'   => [
                ['label' => 'Why do you want to join?', 'value' => 'Because AOD is great'],
            ],
        ]);

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('pendingUser.application.0.label', 'Why do you want to join?')
                ->where('pendingUser.application.0.value', 'Because AOD is great'));
    }

    #[Test]
    public function shows_not_found_state_for_unknown_discord_id(): void
    {
        $this->confirm($this->createOfficer(), '999999999999999999')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('pendingUser', null)
                ->where('memberMatches', []));
    }

    #[Test]
    public function excludes_pending_users_without_date_of_birth(): void
    {
        User::factory()->pending()->create([
            'discord_id'    => '123456789012345678',
            'date_of_birth' => null,
        ]);

        $this->confirm($this->createOfficer(), '123456789012345678')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('pendingUser', null));
    }

    #[Test]
    public function member_cannot_access_discord_confirm(): void
    {
        $division   = $this->createActiveDivision();
        $user       = $this->createMemberWithUser(['division_id' => $division->id]);
        $user->role = Role::MEMBER;
        $user->save();

        $this->confirm($user, '123456789012345678')->assertForbidden();
    }

    #[Test]
    public function calls_out_ex_member_match_with_recruit_options(): void
    {
        $this->createActiveDivision();
        $exMember = $this->createMember([
            'discord_id'  => '123456789012345678',
            'division_id' => 0,
            'name'        => 'FormerMember',
        ]);

        $this->confirm($this->createOfficer(), '123456789012345678')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('memberMatches.0.name', 'FormerMember')
                ->where('memberMatches.0.isExMember', true)
                ->where('memberMatches.0.clan_id', $exMember->clan_id)
                ->has('divisions'));
    }

    #[Test]
    public function calls_out_active_member_match_without_recruit_options(): void
    {
        $division = $this->createActiveDivision(['name' => 'Active Division']);

        $this->createMember([
            'discord_id'  => '123456789012345678',
            'division_id' => $division->id,
            'name'        => 'CurrentMember',
        ]);

        $this->confirm($this->createOfficer(), '123456789012345678')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('memberMatches.0.name', 'CurrentMember')
                ->where('memberMatches.0.isExMember', false)
                ->where('memberMatches.0.division', 'Active Division'));
    }

    #[Test]
    public function does_not_check_members_table_when_pending_registration_found(): void
    {
        $pendingUser = User::factory()->pending()->create(['discord_id' => '123456789012345678']);

        $this->createMember([
            'discord_id'  => '123456789012345678',
            'division_id' => 0,
        ]);

        $this->confirm($this->createOfficer(), $pendingUser->discord_id)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('memberMatches', null));
    }
}
