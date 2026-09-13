<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionKeepAliveTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_a_future_expiry_for_an_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('session.keep-alive'))
            ->assertOk()
            ->assertJsonStructure(['expiresAt'])
            ->assertJson(fn ($json) => $json->where('expiresAt', fn ($ts) => $ts > now()->timestamp));
    }

    #[Test]
    public function it_is_unavailable_to_guests(): void
    {
        $this->getJson(route('session.keep-alive'))->assertUnauthorized();
    }
}
