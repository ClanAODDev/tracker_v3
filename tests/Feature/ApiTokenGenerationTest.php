<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ApiTokenGenerationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_have_tokens_generated()
    {
        $this->signIn();

        $this->assertCount(0, auth()->user()->tokens);

        auth()->user()->createToken('test');

        $this->assertCount(1, auth()->user()->refresh()->tokens);
    }

    /** @test */
    public function a_user_can_have_tokens_revoked()
    {
        $this->signIn();

        auth()->user()->createToken('test');

        $this->assertCount(1, auth()->user()->tokens);

        auth()->user()->tokens()->delete();

        $this->assertCount(0, auth()->user()->refresh()->tokens);
    }

    /** @test */
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

    /** @test */
    public function a_non_officer_cannot_create_api_tokens()
    {
        $this->markTestSkipped('Temporary ACL change');

        $user = User::factory()->create();

        $this->signIn($user);

        $this->withoutExceptionHandling()
            ->get(route('developer'))
            ->assertForbidden();
    }

    /** @test */
    public function a_token_name_is_required_when_generating_an_api_token()
    {
        $this->markTestSkipped('Temporary ACL change');

        $user = User::factory()->officer()->create();

        $this->signIn($user);

        $this->post(route('developer.token.store', []))
            ->assertSessionHasErrors('token_name');
    }

    /** @test */
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
