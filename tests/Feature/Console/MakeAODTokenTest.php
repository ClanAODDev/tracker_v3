<?php

namespace Tests\Feature\Console;

use Tests\TestCase;

class MakeAODTokenTest extends TestCase
{
    public function test_command_generates_token(): void
    {
        config(['aod.token' => 'test-secret']);

        $this->artisan('tracker:make-token')
            ->assertSuccessful()
            ->expectsOutput('Token generated (valid for 1 minute):')
            ->expectsOutput('Example usage:');
    }

    public function test_token_is_deterministic_within_same_minute(): void
    {
        config(['aod.token' => 'test-secret']);

        $currentMinute = floor(time() / 60) * 60;
        $expectedToken = md5($currentMinute . 'test-secret');

        $this->artisan('tracker:make-token')
            ->assertSuccessful()
            ->expectsOutputToContain($expectedToken);
    }
}
