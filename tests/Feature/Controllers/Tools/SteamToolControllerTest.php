<?php

namespace Tests\Feature\Controllers\Tools;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class SteamToolControllerTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.steam.api_key' => 'test-key']);
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $this->postJson(route('tools.steam.resolve-vanity-url'), ['input' => 'gaben'])
            ->assertUnauthorized();
    }

    #[Test]
    public function it_resolves_a_bare_vanity_name(): void
    {
        Http::fake([
            'api.steampowered.com/*' => Http::response([
                'response' => ['success' => 1, 'steamid' => '76561197960287930'],
            ]),
        ]);

        $this->actingAs($this->createMemberWithUser())
            ->postJson(route('tools.steam.resolve-vanity-url'), ['input' => 'gaben'])
            ->assertOk()
            ->assertJson(['steamId' => '76561197960287930', 'resolved' => true]);

        Http::assertSent(fn ($request) => $request['vanityurl'] === 'gaben' && $request['key'] === 'test-key');
    }

    #[Test]
    public function it_extracts_the_vanity_name_from_a_profile_url(): void
    {
        Http::fake([
            'api.steampowered.com/*' => Http::response([
                'response' => ['success' => 1, 'steamid' => '76561197960287930'],
            ]),
        ]);

        $this->actingAs($this->createMemberWithUser())
            ->postJson(route('tools.steam.resolve-vanity-url'), [
                'input' => 'https://steamcommunity.com/id/gabelogannewell/',
            ])
            ->assertOk();

        Http::assertSent(fn ($request) => $request['vanityurl'] === 'gabelogannewell');
    }

    #[Test]
    public function it_returns_an_already_numeric_id_without_calling_steam(): void
    {
        Http::fake();

        $this->actingAs($this->createMemberWithUser())
            ->postJson(route('tools.steam.resolve-vanity-url'), ['input' => '76561197960287930'])
            ->assertOk()
            ->assertJson(['steamId' => '76561197960287930', 'resolved' => false]);

        Http::assertNothingSent();
    }

    #[Test]
    public function it_extracts_a_numeric_id_from_a_profiles_url_without_calling_steam(): void
    {
        Http::fake();

        $this->actingAs($this->createMemberWithUser())
            ->postJson(route('tools.steam.resolve-vanity-url'), [
                'input' => 'https://steamcommunity.com/profiles/76561197960287930',
            ])
            ->assertOk()
            ->assertJson(['steamId' => '76561197960287930', 'resolved' => false]);

        Http::assertNothingSent();
    }

    #[Test]
    public function it_returns_a_404_when_steam_finds_no_match(): void
    {
        Http::fake([
            'api.steampowered.com/*' => Http::response([
                'response' => ['success' => 42, 'message' => 'No match'],
            ]),
        ]);

        $this->actingAs($this->createMemberWithUser())
            ->postJson(route('tools.steam.resolve-vanity-url'), ['input' => 'not-a-real-vanity-name'])
            ->assertNotFound()
            ->assertJson(['message' => 'No match']);
    }

    #[Test]
    public function it_fails_gracefully_when_no_api_key_is_configured(): void
    {
        config(['services.steam.api_key' => null]);

        $this->actingAs($this->createMemberWithUser())
            ->postJson(route('tools.steam.resolve-vanity-url'), ['input' => 'gaben'])
            ->assertStatus(500);
    }
}
