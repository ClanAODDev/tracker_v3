<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class JsonApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_tokenless_user_cannot_access_api_routes()
    {
        $this->json('get', route('v1.divisions.index'))
            ->assertStatus(401);
    }

    /** @test */
    public function a_tokenful_user_can_access_api_routes()
    {
        Sanctum::actingAs(
            User::factory()->officer()->create(),
            ['*']
        );

        $this->json('get', route('v1.divisions.index'))
            ->assertStatus(200);
    }
}
