<?php

namespace Tests\Feature\Controllers;

use App\Enums\ForumGroup;
use App\Enums\Role;
use App\Models\Member;
use App\Models\User;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class DiscordAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function mockDiscordUser(array $attributes = []): void
    {
        $defaults = [
            'id' => '123456789',
            'nickname' => 'TestUser',
            'name' => 'Test User',
            'email' => 'test@discord.com',
        ];

        $attributes = array_merge($defaults, $attributes);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($attributes['id']);
        $socialiteUser->shouldReceive('getNickname')->andReturn($attributes['nickname']);
        $socialiteUser->shouldReceive('getName')->andReturn($attributes['name']);
        $socialiteUser->shouldReceive('getEmail')->andReturn($attributes['email']);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('discord')
            ->andReturn($provider);
    }

    public function test_discord_redirect_redirects_to_discord(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirect')
            ->andReturn(redirect('https://discord.com/oauth2/authorize'));

        Socialite::shouldReceive('driver')
            ->with('discord')
            ->andReturn($provider);

        $response = $this->get(route('auth.discord'));

        $response->assertRedirect();
    }

    public function test_discord_callback_rejects_unknown_discord_id(): void
    {
        $this->mockDiscordUser([
            'id' => '999888777',
            'nickname' => 'NewUser',
            'email' => 'new@discord.com',
        ]);

        $response = $this->get(route('auth.discord.callback'));

        $this->assertDatabaseMissing('users', ['discord_id' => '999888777']);
        $this->assertDatabaseMissing('members', ['discord_id' => '999888777']);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('discord');
    }

    public function test_discord_callback_finds_existing_member_by_discord_id(): void
    {
        $member = Member::factory()->create(['discord_id' => '111222333']);

        $this->mockDiscordUser([
            'id' => '111222333',
            'nickname' => 'ExistingMember',
            'email' => 'existing@discord.com',
        ]);

        $response = $this->get(route('auth.discord.callback'));

        $user = User::where('member_id', $member->id)->first();
        $this->assertNotNull($user);
        $this->assertEquals('111222333', $user->discord_id);
        $this->assertAuthenticated();

        $response->assertRedirect('/');
    }

    public function test_discord_callback_uses_existing_user_with_discord_id(): void
    {
        $member = Member::factory()->create(['discord_id' => '777888999']);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'discord_id' => '777888999',
            'discord_username' => 'existinguser',
        ]);

        $this->mockDiscordUser([
            'id' => '777888999',
            'email' => 'new@discord.com',
        ]);

        $response = $this->get(route('auth.discord.callback'));

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    public function test_discord_callback_redirects_pending_user_to_pending_page(): void
    {
        $user = User::factory()->pending()->create([
            'discord_id' => '555666777',
            'discord_username' => 'pendinguser',
        ]);

        $this->mockDiscordUser([
            'id' => '555666777',
            'email' => 'pending@discord.com',
        ]);

        $response = $this->get(route('auth.discord.callback'));

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('auth.discord.pending'));
    }

    // @todo Re-enable when new account creation is enabled
    // These tests previously validated email requirements for new user creation.
    // With account creation disabled, unknown discord IDs are rejected before email validation.
    // public function test_discord_callback_rejects_new_user_without_email(): void
    // {
    //     $this->mockDiscordUser([
    //         'id' => '123123123',
    //         'nickname' => 'NoEmail',
    //         'email' => null,
    //     ]);
    //
    //     $response = $this->get(route('auth.discord.callback'));
    //
    //     $response->assertRedirect(route('login'));
    //     $response->assertSessionHasErrors('discord');
    //
    //     $this->assertDatabaseMissing('users', ['discord_id' => '123123123']);
    // }
    //
    // public function test_discord_callback_rejects_duplicate_email(): void
    // {
    //     User::factory()->create(['email' => 'taken@example.com']);
    //
    //     $this->mockDiscordUser([
    //         'id' => '321321321',
    //         'nickname' => 'DupeEmail',
    //         'email' => 'taken@example.com',
    //     ]);
    //
    //     $response = $this->get(route('auth.discord.callback'));
    //
    //     $response->assertRedirect(route('login'));
    //     $response->assertSessionHasErrors('discord');
    //
    //     $this->assertDatabaseMissing('users', ['discord_id' => '321321321']);
    // }

    // @todo Re-enable when new account creation is enabled
    // public function test_discord_callback_sanitizes_username(): void
    // {
    //     $this->mockDiscordUser([
    //         'id' => '444555666',
    //         'nickname' => 'Test@User#123!',
    //         'email' => 'test@example.com',
    //     ]);
    //
    //     $response = $this->get(route('auth.discord.callback'));
    //
    //     $user = User::where('discord_id', '444555666')->first();
    //     $this->assertEquals('TestUser123', $user->name);
    //
    //     $response->assertRedirect(route('auth.discord.pending'));
    // }
    //
    // public function test_discord_callback_uses_name_when_nickname_null(): void
    // {
    //     $this->mockDiscordUser([
    //         'id' => '666777888',
    //         'nickname' => null,
    //         'name' => 'DiscordName',
    //         'email' => 'name@example.com',
    //     ]);
    //
    //     $response = $this->get(route('auth.discord.callback'));
    //
    //     $user = User::where('discord_id', '666777888')->first();
    //     $this->assertEquals('DiscordName', $user->discord_username);
    //
    //     $response->assertRedirect(route('auth.discord.pending'));
    // }

    public function test_discord_callback_links_discord_to_existing_member_user(): void
    {
        $member = Member::factory()->create(['discord_id' => '888999000']);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'email' => 'existing@example.com',
        ]);

        $this->mockDiscordUser([
            'id' => '888999000',
            'nickname' => 'LinkedUser',
            'email' => 'new@discord.com',
        ]);

        $this->get(route('auth.discord.callback'));

        $user->refresh();
        $this->assertEquals('888999000', $user->discord_id);
        $this->assertEquals('LinkedUser', $user->discord_username);
    }

    public function test_pending_page_requires_authentication(): void
    {
        $response = $this->get(route('auth.discord.pending'));

        $response->assertRedirect(route('login'));
    }

    public function test_pending_page_redirects_completed_users_to_home(): void
    {
        $member = Member::factory()->create();
        $user = User::factory()->create([
            'member_id' => $member->id,
            'discord_id' => '123456789',
        ]);

        $response = $this->actingAs($user)->get(route('auth.discord.pending'));

        $response->assertRedirect('/');
    }

    public function test_pending_page_displays_for_pending_users(): void
    {
        $user = User::factory()->pending()->create([
            'discord_id' => '123456789',
            'discord_username' => 'PendingUser',
        ]);

        $response = $this->actingAs($user)->get(route('auth.discord.pending'));

        $response->assertOk();
        $response->assertSee('PendingUser');
        $response->assertSee('ClanAOD Registration');
    }

    public function test_user_is_pending_registration_returns_true_for_discord_only(): void
    {
        $user = User::factory()->pending()->create([
            'discord_id' => '123456789',
        ]);

        $this->assertTrue($user->isPendingRegistration());
    }

    public function test_user_is_pending_registration_returns_false_for_linked_member(): void
    {
        $member = Member::factory()->create();
        $user = User::factory()->create([
            'discord_id' => '123456789',
            'member_id' => $member->id,
        ]);

        $this->assertFalse($user->isPendingRegistration());
    }

    public function test_user_has_member_returns_correct_value(): void
    {
        $member = Member::factory()->create();
        $userWithMember = User::factory()->create(['member_id' => $member->id]);
        $userWithoutMember = User::factory()->pending()->create();

        $this->assertTrue($userWithMember->hasMember());
        $this->assertFalse($userWithoutMember->hasMember());
    }

    public function test_pending_user_is_redirected_to_pending_page_on_other_routes(): void
    {
        $user = User::factory()->pending()->create();

        $response = $this->actingAs($user)->get('/home');

        $response->assertRedirect(route('auth.discord.pending'));
    }

    public function test_pending_user_can_logout(): void
    {
        $user = User::factory()->pending()->create();

        $response = $this->actingAs($user)->get(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_pending_discord_scope_returns_only_pending_users(): void
    {
        $member = Member::factory()->create();
        User::factory()->create(['member_id' => $member->id, 'discord_id' => '111']);
        User::factory()->create(['member_id' => null, 'discord_id' => null]);
        $pendingUser1 = User::factory()->pending()->create();
        $pendingUser2 = User::factory()->pending()->create();

        $pendingUsers = User::pendingDiscord()->get();

        $this->assertCount(2, $pendingUsers);
        $this->assertTrue($pendingUsers->contains($pendingUser1));
        $this->assertTrue($pendingUsers->contains($pendingUser2));
    }

    public function test_discord_callback_syncs_forum_roles_for_new_user(): void
    {
        $member = Member::factory()->create([
            'discord_id' => '123456789',
            'clan_id' => 99999,
        ]);

        $this->mockDiscordUser([
            'id' => '123456789',
            'nickname' => 'SeniorLeader',
            'email' => 'leader@example.com',
        ]);

        $this->mock(ForumProcedureService::class, function ($mock) {
            $mock->shouldReceive('getUser')
                ->with(99999)
                ->andReturn((object) [
                    'usergroupid' => ForumGroup::SERGEANT->value,
                    'membergroupids' => '',
                ]);
        });

        $this->get(route('auth.discord.callback'));

        $user = User::where('member_id', $member->id)->first();
        $this->assertNotNull($user);
        $this->assertEquals(Role::SENIOR_LEADER, $user->role);
    }

    public function test_discord_callback_syncs_forum_roles_for_existing_user(): void
    {
        $member = Member::factory()->create([
            'discord_id' => '987654321',
            'clan_id' => 88888,
        ]);
        $user = User::factory()->create([
            'member_id' => $member->id,
            'discord_id' => '987654321',
            'role' => Role::MEMBER,
        ]);

        $this->mockDiscordUser([
            'id' => '987654321',
            'nickname' => 'PromotedOfficer',
            'email' => 'officer@example.com',
        ]);

        $this->mock(ForumProcedureService::class, function ($mock) {
            $mock->shouldReceive('getUser')
                ->with(88888)
                ->andReturn((object) [
                    'usergroupid' => ForumGroup::ADMIN->value,
                    'membergroupids' => '',
                ]);
        });

        $this->get(route('auth.discord.callback'));

        $user->refresh();
        $this->assertEquals(Role::ADMIN, $user->role);
    }
}
