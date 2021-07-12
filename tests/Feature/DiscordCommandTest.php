<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DiscordCommandTest extends TestCase
{
    /** @test */
    public function a_slack_command_with_a_valid_token_returns_successful()
    {
        $token = 'a-test-token';

        config()->set('slack.tokens', $token);

        $response = $this->json('POST', '/slack', [
            'token' => $token,
            'text' => 'help'
        ]);

        $response->assertSeeText('The following commands are currently available.');
    }

    /** @test */
    public function an_invalid_slack_command_with_a_known_token_fails()
    {
        $token = 'a-test-token';

        config()->set('slack.tokens', $token);

        $response = $this->json('POST', '/slack', [
            'token' => $token,
            'text' => 'foo'
        ]);
        
        $response->assertSeeText('Unrecognized command');
    }

    /** @test */
    public function a_slack_command_fails_if_it_has_no_known_token()
    {
        $response = $this->json('POST', 'slack');

        $response->assertStatus(401);
    }
}
