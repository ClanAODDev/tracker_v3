<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiTokenGenerationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_user_can_have_tokens_generated()
    {
        $this->signIn();

        $this->assertCount(0, auth()->user()->tokens);

        auth()->user()->createToken('test');

        $this->assertCount(1, auth()->user()->refresh()->tokens);
    }

    #[Test]
    public function developer_page_renders_the_inertia_page_with_tokens(): void
    {
        $user = User::factory()->create(['developer' => true]);
        $user->createToken('existing');

        $this->actingAs($user)
            ->get(route('developer'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('developer/index')
                ->has('tokens', 1)
                ->where('tokens.0.name', 'existing'));
    }

    #[Test]
    public function a_basic_developer_is_not_offered_advanced_scopes(): void
    {
        $user = User::factory()->create(['developer' => true, 'role' => Role::OFFICER]);

        $this->actingAs($user)
            ->get(route('developer'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('developer/index')
                ->where('availableScopes', fn ($scopes) => collect($scopes)->pluck('value')->all() === [
                    'clan:read', 'division:read',
                ]));
    }

    #[Test]
    public function a_senior_leader_developer_is_offered_advanced_scopes(): void
    {
        $user = User::factory()->create(['developer' => true, 'role' => Role::SENIOR_LEADER]);

        $this->actingAs($user)
            ->get(route('developer'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('developer/index')
                ->where('availableScopes', fn ($scopes) => collect($scopes)->pluck('value')->all() === [
                    'clan:read', 'division:read', 'division:read-advanced', 'division:write',
                ]));
    }

    #[Test]
    public function a_developer_cannot_generate_a_token_with_a_scope_outside_their_access(): void
    {
        $user = User::factory()->create(['developer' => true, 'role' => Role::OFFICER]);

        $this->actingAs($user)
            ->post(route('developer.token.store'), [
                'token_name' => 'test',
                'scopes'     => ['division:write'],
            ])
            ->assertSessionHasErrors('scopes.0');

        $this->assertCount(0, $user->refresh()->tokens);
    }

    #[Test]
    public function a_developer_can_generate_a_token_with_an_allowed_scope(): void
    {
        $user = User::factory()->create(['developer' => true, 'role' => Role::SENIOR_LEADER]);

        $this->actingAs($user)
            ->post(route('developer.token.store'), [
                'token_name' => 'test',
                'scopes'     => ['division:read', 'division:read-advanced'],
            ])
            ->assertRedirect(route('developer'));

        $token = $user->refresh()->tokens->first();
        $this->assertSame(['division:read', 'division:read-advanced'], $token->abilities);
    }

    #[Test]
    public function a_developer_can_update_an_existing_tokens_scopes(): void
    {
        $user  = User::factory()->create(['developer' => true, 'role' => Role::SENIOR_LEADER]);
        $token = $user->createToken('existing', ['division:read']);

        $this->actingAs($user)
            ->patch(route('developer.token.update'), [
                'token_id' => $token->accessToken->id,
                'scopes'   => ['division:read', 'division:write'],
            ])
            ->assertRedirect(route('developer'));

        $this->assertSame(['division:read', 'division:write'], $token->accessToken->fresh()->abilities);
    }

    #[Test]
    public function a_developer_cannot_update_a_tokens_scopes_beyond_their_own_access(): void
    {
        $user  = User::factory()->create(['developer' => true, 'role' => Role::OFFICER]);
        $token = $user->createToken('existing', ['division:read']);

        $this->actingAs($user)
            ->patch(route('developer.token.update'), [
                'token_id' => $token->accessToken->id,
                'scopes'   => ['division:write'],
            ])
            ->assertSessionHasErrors('scopes.0');

        $this->assertSame(['division:read'], $token->accessToken->fresh()->abilities);
    }

    #[Test]
    public function a_developer_cannot_update_another_users_token_scopes(): void
    {
        $owner = User::factory()->create(['developer' => true, 'role' => Role::SENIOR_LEADER]);
        $other = User::factory()->create(['developer' => true, 'role' => Role::SENIOR_LEADER]);
        $token = $owner->createToken('existing', ['division:read']);

        $this->actingAs($other)
            ->patch(route('developer.token.update'), [
                'token_id' => $token->accessToken->id,
                'scopes'   => ['division:write'],
            ])
            ->assertNotFound();

        $this->assertSame(['division:read'], $token->accessToken->fresh()->abilities);
    }

    #[Test]
    public function a_user_can_have_tokens_revoked()
    {
        $this->signIn();

        auth()->user()->createToken('test');

        $this->assertCount(1, auth()->user()->tokens);

        auth()->user()->tokens()->delete();

        $this->assertCount(0, auth()->user()->refresh()->tokens);
    }

    #[Test]
    public function an_officer_can_create_api_tokens()
    {
        $this->markTestSkipped('Temporary ACL change');

        $user = User::factory()->officer()->create();

        $this->signIn($user);

        $this->get(route('developer'))
            ->assertOk();

        $this->post(route('developer.token.store', ['token_name' => 'test']))
            ->assertRedirect(route('developer'));

        $this->assertCount(1, $user->refresh()->tokens);
    }

    #[Test]
    public function a_non_officer_cannot_create_api_tokens()
    {
        $this->markTestSkipped('Temporary ACL change');

        $user = User::factory()->create();

        $this->signIn($user);

        $this->withoutExceptionHandling()
            ->get(route('developer'))
            ->assertForbidden();
    }

    #[Test]
    public function a_token_name_is_required_when_generating_an_api_token()
    {
        $this->markTestSkipped('Temporary ACL change');

        $user = User::factory()->officer()->create();

        $this->signIn($user);

        $this->post(route('developer.token.store', []))
            ->assertSessionHasErrors('token_name');
    }

    #[Test]
    public function an_officer_can_revoke_their_own_token()
    {
        $this->markTestSkipped('Temporary ACL change');

        $user = User::factory()->officer()->create();

        $this->signIn($user);

        $token = $user->createToken('test');

        $this->delete(route(
            'developer.token.delete',
            ['token_id' => $token->accessToken->id]
        ));

        $this->assertCount(0, $user->refresh()->tokens);
    }
}
