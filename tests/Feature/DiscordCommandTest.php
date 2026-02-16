<?php

namespace Tests\Feature;

use App\Models\Division;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DiscordCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_discord_command_with_a_valid_token_returns_successful()
    {
        Division::factory()->create([
            'name'         => 'Planetside',
            'abbreviation' => 'ps2',
            'active'       => true,
        ]);

        $token = 'a-test-token';

        config()->set('aod.bot_cmd_tokens', $token);

        $response = $this->json('GET', '/bot/commands/division?value=ps2', [
            'token' => $token,
        ]);

        $response->assertJson(
            fn (AssertableJson $json) => $json->where(
                'embed.author.name',
                'Planetside'
            )->etc()
        );
    }

    #[Test]
    public function an_invalid_discord_command_with_a_known_token_fails()
    {
        $token = 'a-test-token';

        config()->set('aod.bot_cmd_tokens', $token);

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

    #[Test]
    public function a_discord_command_fails_if_it_has_no_known_token()
    {
        $response = $this->json('GET', '/bot/commands/member');

        $response->assertStatus(401);
    }
}
