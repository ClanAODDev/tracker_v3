<?php

namespace Tests\Feature\Controllers;

use App\Models\Member;
use App\Models\User;
use App\Services\AODForumService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ForumLoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_form_renders_the_inertia_page(): void
    {
        User::factory()->create();

        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('auth/login')
                ->has('discordEnabled'));
    }

    #[Test]
    public function failed_login_returns_validation_errors(): void
    {
        User::factory()->create();

        $this->mock(AODForumService::class)
            ->shouldReceive('authenticate')
            ->andReturn(null);

        $this->from('/login')
            ->post('/login', ['username' => 'nobody', 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    #[Test]
    public function repeated_failed_logins_are_locked_out(): void
    {
        User::factory()->create();

        $this->mock(AODForumService::class)
            ->shouldReceive('authenticate')
            ->andReturn(null);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['username' => 'spammer', 'password' => 'wrong']);
        }

        $this->from('/login')
            ->post('/login', ['username' => 'spammer', 'password' => 'wrong'])
            ->assertStatus(302)
            ->assertSessionHasErrors('username');

        $this->assertStringContainsString(
            'Too many login attempts',
            session('errors')->first('username'),
        );
    }

    #[Test]
    public function login_uses_clan_id_not_name_to_find_member(): void
    {
        $member1 = Member::factory()->create([
            'clan_id' => 11111,
            'name'    => 'DuplicateName',
        ]);
        $member2 = Member::factory()->create([
            'clan_id' => 22222,
            'name'    => 'DuplicateName',
        ]);

        $user1 = User::factory()->create(['member_id' => $member1->id]);
        $user2 = User::factory()->create(['member_id' => $member2->id]);

        $this->mock(AODForumService::class)
            ->shouldReceive('authenticate')
            ->andReturn([
                'clan_id' => 22222,
                'email'   => 'test@example.com',
                'roles'   => [2],
            ]);

        $this->post('/login', [
            'username' => 'DuplicateName',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user2);
    }
}
