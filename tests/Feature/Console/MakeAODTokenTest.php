<?php

namespace Tests\Feature\Console;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MakeAODTokenTest extends TestCase
{
    #[Test]
    public function command_generates_token(): void
    {
        config(['aod.token' => 'test-secret']);

        $this->artisan('tracker:make-token')
            ->assertSuccessful()
            ->expectsOutput('Token generated (valid for 1 minute):')
            ->expectsOutput('Example usage:');
    }

    #[Test]
    public function token_is_deterministic_within_same_minute(): void
    {
        config(['aod.token' => 'test-secret']);

        $currentMinute = floor(time() / 60) * 60;
        $expectedToken = md5($currentMinute . 'test-secret');

        $this->artisan('tracker:make-token')
            ->assertSuccessful()
            ->expectsOutputToContain($expectedToken);
    }
}
