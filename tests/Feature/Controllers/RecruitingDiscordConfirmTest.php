<?php

namespace Tests\Feature\Controllers;

use App\Enums\Role;
use App\Models\DivisionApplication;
use App\Models\User;
use App\Services\AODForumService;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RecruitingDiscordConfirmTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function shows_matching_pending_registration(): void
    {
        $officer = $this->createOfficer();

        $pendingUser = User::factory()->pending()->create([
            'discord_id'       => '123456789012345678',
            'discord_username' => 'ReadyUser',
            'email'            => 'readyuser@example.com',
        ]);

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', $pendingUser->discord_id));

        $response->assertOk();
        $response->assertViewIs('recruit.discord-confirm');
        $response->assertViewHas('pendingUser', fn ($data) => $data['discord_username'] === 'ReadyUser'
            && $data['obfuscated_email'] === '***er@example.com');
        $response->assertSee('ReadyUser');
        $response->assertSee('***er@example.com');
    }

    #[Test]
    public function targets_the_division_from_the_pending_application(): void
    {
        $officer  = $this->createOfficer();
        $division = $this->createActiveDivision();

        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789012345678',
        ]);

        DivisionApplication::factory()->create([
            'user_id'     => $pendingUser->id,
            'division_id' => $division->id,
        ]);

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', $pendingUser->discord_id));

        $response->assertOk();
        $response->assertSee($division->name . ' Division');
        $response->assertSee(route('recruiting.form', $division) . '?pending_user_id=' . $pendingUser->id, false);
    }

    #[Test]
    public function shows_division_picker_when_no_application_on_file(): void
    {
        $officer   = $this->createOfficer();
        $divisionA = $this->createActiveDivision(['name' => 'Alpha Division']);
        $divisionB = $this->createActiveDivision(['name' => 'Bravo Division']);

        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789012345678',
        ]);

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', $pendingUser->discord_id));

        $response->assertOk();
        $response->assertSee('No division application on file');
        $response->assertSee(route('recruiting.form', $divisionA) . '?pending_user_id=' . $pendingUser->id, false);
        $response->assertSee(route('recruiting.form', $divisionB) . '?pending_user_id=' . $pendingUser->id, false);
    }

    #[Test]
    public function redirects_when_applied_division_is_shutdown(): void
    {
        $officer  = $this->createOfficer();
        $division = $this->createActiveDivision(['shutdown_at' => now()->subDay()]);

        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789012345678',
        ]);

        DivisionApplication::factory()->create([
            'user_id'     => $pendingUser->id,
            'division_id' => $division->id,
        ]);

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', $pendingUser->discord_id));

        $response->assertRedirect();
    }

    #[Test]
    public function shows_forum_account_when_found_and_eligible(): void
    {
        $officer = $this->createOfficer();

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

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', $pendingUser->discord_id));

        $response->assertOk();
        $response->assertSee('Forum account found');
        $response->assertSee('ForumUser555');
    }

    #[Test]
    public function shows_ineligible_reason_when_forum_account_blocked(): void
    {
        $officer = $this->createOfficer();

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

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', $pendingUser->discord_id));

        $response->assertOk();
        $response->assertSee('BannedUser');
        $response->assertSee('User forum account is banned');
    }

    #[Test]
    public function shows_no_forum_account_message_when_not_found(): void
    {
        $officer = $this->createOfficer();

        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789012345678',
            'email'      => 'recruit@example.com',
        ]);

        $this->mock(AODForumService::class, function ($mock) {
            $mock->shouldReceive('getUserByEmail')->andReturn(null);
        });

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', $pendingUser->discord_id));

        $response->assertOk();
        $response->assertSee('No existing forum account found');
    }

    #[Test]
    public function shows_application_responses_when_present(): void
    {
        $officer  = $this->createOfficer();
        $division = $this->createActiveDivision();

        $pendingUser = User::factory()->pending()->create([
            'discord_id' => '123456789012345678',
        ]);

        DivisionApplication::factory()->create([
            'user_id'     => $pendingUser->id,
            'division_id' => $division->id,
            'responses'   => [
                ['label' => 'Why do you want to join?', 'value' => 'Because AOD is great'],
            ],
        ]);

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', $pendingUser->discord_id));

        $response->assertOk();
        $response->assertSee('Why do you want to join?');
        $response->assertSee('Because AOD is great');
    }

    #[Test]
    public function shows_not_found_message_for_unknown_discord_id(): void
    {
        $officer = $this->createOfficer();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', '999999999999999999'));

        $response->assertOk();
        $response->assertViewHas('pendingUser', null);
        $response->assertSee('No Pending Registration Found');
        $response->assertSee('clanaod.net');
    }

    #[Test]
    public function excludes_pending_users_without_date_of_birth(): void
    {
        $officer = $this->createOfficer();

        User::factory()->pending()->create([
            'discord_id'    => '123456789012345678',
            'date_of_birth' => null,
        ]);

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', '123456789012345678'));

        $response->assertOk();
        $response->assertViewHas('pendingUser', null);
    }

    #[Test]
    public function member_cannot_access_discord_confirm(): void
    {
        $division   = $this->createActiveDivision();
        $user       = $this->createMemberWithUser(['division_id' => $division->id]);
        $user->role = Role::MEMBER;
        $user->save();

        $response = $this->actingAs($user)
            ->get(route('recruiting.discordConfirm', '123456789012345678'));

        $response->assertForbidden();
    }
}
