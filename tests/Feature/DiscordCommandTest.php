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

        config()->set('slack.tokens', $token);

        $response = $this->json('POST', '/slack', [
            'token' => $token,
            'text' => 'help',
        ]);

        $response->assertJson(
            fn (AssertableJson $json) => $json->where(
                'embed.title',
                'The following commands are currently available.'
            )->etc()
        );
    }

    /** @test */
    public function an_invalid_slack_command_with_a_known_token_fails()
    {
        $token = 'a-test-token';

        config()->set('slack.tokens', $token);

        $response = $this->json('POST', '/slack', [
            'token' => $token,
            'text' => 'foo',
        ]);

        $response->assertJson(
            fn (AssertableJson $json) => $json->where(
                'text',
                'Unrecognized command. Sorry!'
            )
        );
    }

    /** @test */
    public function a_slack_command_fails_if_it_has_no_known_token()
    {
        $response = $this->json('POST', 'slack');

        $response->assertStatus(401);
    }
}
