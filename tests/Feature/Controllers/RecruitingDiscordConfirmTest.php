<?php

namespace Tests\Feature\Controllers;

use App\Enums\Role;
use App\Models\DivisionApplication;
use App\Models\User;
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
        $officer  = $this->createOfficer();
        $division = $this->createActiveDivision();

        $pendingUser = User::factory()->pending()->create([
            'discord_id'       => '123456789012345678',
            'discord_username' => 'ReadyUser',
        ]);

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', [$division->slug, $pendingUser->discord_id]));

        $response->assertOk();
        $response->assertViewIs('recruit.discord-confirm');
        $response->assertViewHas('pendingUser', fn ($data) => $data['discord_username'] === 'ReadyUser');
        $response->assertSee('ReadyUser');
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
            ->get(route('recruiting.discordConfirm', [$division->slug, $pendingUser->discord_id]));

        $response->assertOk();
        $response->assertSee('Why do you want to join?');
        $response->assertSee('Because AOD is great');
    }

    #[Test]
    public function shows_not_found_message_for_unknown_discord_id(): void
    {
        $officer  = $this->createOfficer();
        $division = $this->createActiveDivision();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', [$division->slug, '999999999999999999']));

        $response->assertOk();
        $response->assertViewHas('pendingUser', null);
        $response->assertSee('No pending Discord registration found');
    }

    #[Test]
    public function excludes_pending_users_without_date_of_birth(): void
    {
        $officer  = $this->createOfficer();
        $division = $this->createActiveDivision();

        User::factory()->pending()->create([
            'discord_id'    => '123456789012345678',
            'date_of_birth' => null,
        ]);

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', [$division->slug, '123456789012345678']));

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
            ->get(route('recruiting.discordConfirm', [$division->slug, '123456789012345678']));

        $response->assertForbidden();
    }

    #[Test]
    public function redirects_for_shutdown_division(): void
    {
        $officer               = $this->createOfficer();
        $division              = $this->createActiveDivision();
        $division->shutdown_at = now()->subDay();
        $division->save();

        $response = $this->actingAs($officer)
            ->get(route('recruiting.discordConfirm', [$division->slug, '123456789012345678']));

        $response->assertRedirect();
    }
}
