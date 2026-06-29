<?php

namespace Tests\Feature\Controllers;

use App\AOD\ClanForumPermissions;
use App\Models\Member;
use App\Models\User;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClanForumSessionTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSessionData(int $clanId, string $username, string $email): object
    {
        return (object) [
            'loggedin'       => 1,
            'userid'         => $clanId,
            'username'       => 'aod_' . $username,
            'email'          => $email,
            'membergroupids' => '5',
            'usergroupid'    => 2,
        ];
    }

    private function mockForumServices(object $sessionData): void
    {
        $this->mock(ForumProcedureService::class)
            ->shouldReceive('checkSession')
            ->andReturn($sessionData);

        $this->mock(ClanForumPermissions::class)
            ->shouldReceive('handleAccountRoles');
    }

    private function hitLoginWithCookie(): void
    {
        $this->withCookie('aod_sessionhash', 'valid_hash')->get('/login');
    }

    #[Test]
    public function creates_new_user_on_first_login(): void
    {
        $member = Member::factory()->create(['clan_id' => 10001]);
        User::factory()->create();

        $sessionData = $this->fakeSessionData(10001, 'newmember', 'new@example.com');
        $this->mockForumServices($sessionData);

        $this->hitLoginWithCookie();

        $this->assertDatabaseHas('users', [
            'name'      => 'newmember',
            'email'     => 'new@example.com',
            'member_id' => $member->id,
        ]);
    }

    #[Test]
    public function returns_existing_user_found_by_username(): void
    {
        $member = Member::factory()->create(['clan_id' => 10002]);
        $user   = User::factory()->create([
            'name'      => 'existingname',
            'member_id' => $member->id,
        ]);

        $sessionData = $this->fakeSessionData(10002, 'existingname', $user->email);
        $this->mockForumServices($sessionData);

        $this->hitLoginWithCookie();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
    }

    #[Test]
    public function finds_existing_user_by_member_id_when_username_has_changed(): void
    {
        $member = Member::factory()->create(['clan_id' => 10003]);
        $user   = User::factory()->create([
            'name'      => 'oldname',
            'email'     => 'mario@example.com',
            'member_id' => $member->id,
        ]);

        $sessionData = $this->fakeSessionData(10003, 'newname', 'mario@example.com');
        $this->mockForumServices($sessionData);

        $this->hitLoginWithCookie();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
    }
}
