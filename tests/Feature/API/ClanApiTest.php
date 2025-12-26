<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ClanApiTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_discord_count_requires_authentication()
    {
        $response = $this->getJson(route('v1.discord_population'));

        $response->assertUnauthorized();
    }

    public function test_discord_count_requires_clan_read_ability()
    {
        $officer = $this->createOfficer();

        Sanctum::actingAs($officer, ['division:read']);

        $response = $this->getJson(route('v1.discord_population'));

        $response->assertForbidden();
    }

    public function test_discord_count_returns_data_with_clan_read_ability()
    {
        $officer = $this->createOfficer();

        Http::fake([
            '*' => Http::response(['count' => 500], 200),
        ]);

        Sanctum::actingAs($officer, ['clan:read']);

        $response = $this->getJson(route('v1.discord_population'));

        $response->assertOk();
    }

    public function test_stream_events_requires_authentication()
    {
        $response = $this->getJson(route('v1.stream_events'));

        $response->assertUnauthorized();
    }
}
