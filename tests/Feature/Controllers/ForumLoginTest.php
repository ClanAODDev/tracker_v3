<?php

namespace Tests\Feature\Controllers;

use App\Models\Member;
use App\Models\User;
use App\Services\AODForumService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForumLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_uses_clan_id_not_name_to_find_member(): void
    {
        $member1 = Member::factory()->create([
            'clan_id' => 11111,
            'name' => 'DuplicateName',
        ]);
        $member2 = Member::factory()->create([
            'clan_id' => 22222,
            'name' => 'DuplicateName',
        ]);

        $user1 = User::factory()->create(['member_id' => $member1->id]);
        $user2 = User::factory()->create(['member_id' => $member2->id]);

        $this->mock(AODForumService::class)
            ->shouldReceive('authenticate')
            ->andReturn([
                'clan_id' => 22222,
                'email' => 'test@example.com',
                'roles' => [2],
            ]);

        $this->post('/login', [
            'username' => 'DuplicateName',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user2);
    }
}
