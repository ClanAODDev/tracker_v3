<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JsonApiTest extends TestCase
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
        $this->signIn();

        auth()->user()->createToken('Testing');

        $this->json('get', route('v1.divisions.index'))
            ->assertStatus(200);
    }
}
