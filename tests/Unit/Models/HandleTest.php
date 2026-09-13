<?php

namespace Tests\Unit\Models;

use App\Models\Handle;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HandleTest extends TestCase
{
    #[Test]
    public function it_matches_any_value_when_no_regex_is_set()
    {
        $handle = Handle::factory()->make(['regex' => null]);

        $this->assertTrue($handle->matches('anything at all'));
        $this->assertTrue($handle->matches(''));
        $this->assertTrue($handle->matches(null));
    }

    #[Test]
    public function it_matches_values_against_its_regex()
    {
        $handle = Handle::factory()->make(['regex' => '/^[0-9]+$/']);

        $this->assertTrue($handle->matches('76561198000000000'));
        $this->assertFalse($handle->matches('not-numeric'));
    }

    #[Test]
    public function a_blank_value_is_always_considered_a_match()
    {
        $handle = Handle::factory()->make(['regex' => '/^[0-9]+$/']);

        $this->assertTrue($handle->matches(null));
        $this->assertTrue($handle->matches(''));
    }

    #[Test]
    public function a_broken_regex_fails_closed_instead_of_crashing()
    {
        $handle = Handle::factory()->make(['regex' => 'not a valid pattern']);

        $this->assertFalse($handle->matches('anything'));
    }
}
