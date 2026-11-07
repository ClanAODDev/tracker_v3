<?php

namespace Tests\Feature\API;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TokenOwnerAuthorityTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function updateChannel(string $token, string $slug)
    {
        return $this->withToken($token)->postJson(route('v1.divisions.update', $slug), [
            'division_channel' => '323456789012345678',
        ]);
    }

    #[Test]
    public function a_senior_leader_token_can_write(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id], ['role' => Role::SENIOR_LEADER]);
        $token    = $user->createToken('bot', ['division:write'])->plainTextToken;

        $this->updateChannel($token, $division->slug)->assertStatus(202);
    }

    #[Test]
    public function a_write_token_stops_working_after_its_owner_is_demoted(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id], ['role' => Role::SENIOR_LEADER]);
        $token    = $user->createToken('bot', ['division:write'])->plainTextToken;

        $user->update(['role' => Role::OFFICER]);

        $this->updateChannel($token, $division->slug)->assertForbidden();
        $this->assertNotSame('323456789012345678', $division->fresh()->division_channel);
    }

    #[Test]
    public function a_wildcard_token_keeps_only_the_scopes_its_owner_can_mint(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id], ['role' => Role::OFFICER]);
        $token    = $user->createToken('legacy', ['*'])->plainTextToken;

        $this->withToken($token)->getJson(route('v1.divisions.index'))->assertOk();
        $this->updateChannel($token, $division->slug)->assertForbidden();
    }

    #[Test]
    public function a_banned_owner_cannot_use_the_api(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id], ['role' => Role::SENIOR_LEADER]);
        $token    = $user->createToken('bot', ['division:read'])->plainTextToken;

        $user->update(['role' => Role::BANNED]);

        $this->withToken($token)->getJson(route('v1.divisions.index'))->assertForbidden();
    }
}
