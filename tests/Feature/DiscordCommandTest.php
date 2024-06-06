<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class DiscordCommandTest extends TestCase
{
    /** @test */
    public function a_slack_command_with_a_valid_token_returns_successful()
    {
        $token = 'a-test-token';

        config()->set('app.aod.bot_cmd_tokens', $token);

        $response = $this->json('GET', '/bot/commands/division?query=ps2', [
            'token' => $token,
        ]);

        $response->assertJson(
            fn (AssertableJson $json) => $json->where(
                'embed.author.name',
                'Planetside'
            )->etc()
        );
    }

    /** @test */
    public function an_invalid_slack_command_with_a_known_token_fails()
    {
        $token = 'a-test-token';

        config()->set('app.aod.bot_cmd_tokens', $token);

        $response = $this->json('GET', '/bot/commands/foo', [
            'token' => $token,
        ]);

        $response->assertJson(
            fn (AssertableJson $json) => $json->where(
                'message',
                'Unrecognized command. Sorry!'
            )
        );
    }

    /** @test */
    public function a_slack_command_fails_if_it_has_no_known_token()
    {
        $response = $this->json('GET', '/bot/commands/member');

        $response->assertStatus(401);
    }
}
