<?php

namespace Tests\Feature\Console;

use App\Models\Handle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class NormalizeSteamHandlesTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    private Handle $steam;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.steam.api_key' => 'test-key']);
        Sleep::fake();
        Storage::fake('local');

        $this->steam = Handle::create(['label' => 'Steam Profile', 'type' => Handle::STEAM_PROFILE]);
    }

    #[Test]
    public function it_fixes_offline_formats_and_leaves_vanities_for_review(): void
    {
        $this->fakeSteam(vanities: ['gaben' => '76561197960287930'], personas: ['76561197960287930' => 'Rabscuttle', '76561197990820839' => 'Friend']);

        $clean  = $this->steamHandle('76561197960311641');
        $url    = $this->steamHandle('http://steamcommunity.com/profiles/76561197968443902/');
        $friend = $this->steamHandle('30555111');
        $vanity = $this->steamHandle('gaben');
        $junk   = $this->steamHandle('-=312th=- Cowboy');

        $this->artisan('tracker:normalize-steam-handles', ['--delay' => 0])->assertSuccessful();

        $this->assertHandleValue($clean, '76561197960311641');
        $this->assertHandleValue($url, '76561197968443902');
        $this->assertHandleValue($friend, '76561197990820839');
        $this->assertHandleValue($vanity, 'gaben');
        $this->assertHandleValue($junk, '-=312th=- Cowboy');
    }

    #[Test]
    public function it_writes_resolved_vanities_when_asked_except_skipped_rows(): void
    {
        $this->fakeSteam(vanities: ['gaben' => '76561197960287930', 'robin' => '76561197960287931']);

        $gaben = $this->steamHandle('gaben');
        $robin = $this->steamHandle('https://steamcommunity.com/id/robin/');

        $this->artisan('tracker:normalize-steam-handles', ['--delay' => 0, '--include-vanity' => true, '--skip' => [(string) $robin]])
            ->assertSuccessful();

        $this->assertHandleValue($gaben, '76561197960287930');
        $this->assertHandleValue($robin, 'https://steamcommunity.com/id/robin/');
    }

    #[Test]
    public function it_writes_nothing_on_a_dry_run(): void
    {
        $this->fakeSteam();

        $url = $this->steamHandle('https://steamcommunity.com/profiles/76561197968443902');

        $this->artisan('tracker:normalize-steam-handles', ['--delay' => 0, '--dry-run' => true])->assertSuccessful();

        $this->assertHandleValue($url, 'https://steamcommunity.com/profiles/76561197968443902');

        $report = Storage::disk('local')->get(Storage::disk('local')->files()[0]);
        $this->assertStringContainsString('76561197968443902,,fixed', $report);
    }

    #[Test]
    public function it_does_not_convert_a_friend_code_with_no_matching_account(): void
    {
        $this->fakeSteam();

        $friend = $this->steamHandle('30555111');

        $this->artisan('tracker:normalize-steam-handles', ['--delay' => 0])->assertSuccessful();

        $this->assertHandleValue($friend, '30555111');
    }

    #[Test]
    public function it_resolves_each_distinct_vanity_once(): void
    {
        $this->fakeSteam(vanities: ['gaben' => '76561197960287930']);

        $this->steamHandle('gaben');
        $this->steamHandle('GabeN');

        $this->artisan('tracker:normalize-steam-handles', ['--delay' => 0, '--dry-run' => true])->assertSuccessful();

        Http::assertSentCount(2);
    }

    #[Test]
    public function it_fails_but_still_applies_offline_fixes_when_steam_is_down(): void
    {
        Http::fake(['api.steampowered.com/*' => Http::response([], 503)]);

        $url    = $this->steamHandle('https://steamcommunity.com/profiles/76561197968443902');
        $friend = $this->steamHandle('30555111');
        $vanity = $this->steamHandle('gaben');

        $this->artisan('tracker:normalize-steam-handles', ['--delay' => 0])->assertFailed();

        $this->assertHandleValue($url, '76561197968443902');
        $this->assertHandleValue($friend, '30555111');
        $this->assertHandleValue($vanity, 'gaben');
    }

    #[Test]
    public function it_refuses_to_run_without_an_api_key(): void
    {
        config(['services.steam.api_key' => null]);
        Http::fake();

        $url = $this->steamHandle('https://steamcommunity.com/profiles/76561197968443902');

        $this->artisan('tracker:normalize-steam-handles', ['--delay' => 0])->assertFailed();

        $this->assertHandleValue($url, 'https://steamcommunity.com/profiles/76561197968443902');
        Http::assertNothingSent();
    }

    #[Test]
    public function it_lists_unfixable_handles_without_calling_steam_or_writing(): void
    {
        Http::fake();

        $this->steamHandle('76561197960311641');
        $this->steamHandle('https://steamcommunity.com/profiles/76561197968443902');
        $vanity  = $this->steamHandle('AOD_PhoenixATL');
        $invalid = $this->steamHandle('-=312th=- Cowboy');

        $this->artisan('tracker:normalize-steam-handles', ['--unfixable' => true])
            ->expectsOutputToContain('AOD_PhoenixATL')
            ->expectsOutputToContain('Not a SteamID or custom URL name')
            ->doesntExpectOutputToContain('76561197960311641')
            ->doesntExpectOutputToContain('76561197968443902')
            ->expectsOutputToContain('2 members need to re-enter their SteamID64.')
            ->assertSuccessful();

        $this->assertHandleValue($vanity, 'AOD_PhoenixATL');
        $this->assertHandleValue($invalid, '-=312th=- Cowboy');
        Http::assertNothingSent();
    }

    #[Test]
    public function it_limits_the_unfixable_list_to_active_members(): void
    {
        $active   = $this->createMember(['name' => 'ActiveMember']);
        $inactive = $this->createMember(['name' => 'FormerMember', 'division_id' => 0]);

        $active->handles()->attach($this->steam->id, ['value' => 'ActiveVanity']);
        $inactive->handles()->attach($this->steam->id, ['value' => 'FormerVanity']);

        $this->artisan('tracker:normalize-steam-handles', ['--unfixable' => true, '--active' => true])
            ->expectsOutputToContain('ActiveVanity')
            ->doesntExpectOutputToContain('FormerVanity')
            ->assertSuccessful();
    }

    private function fakeSteam(array $vanities = [], array $personas = []): void
    {
        Http::fake(function ($request) use ($vanities, $personas) {
            if (str_contains($request->url(), 'ResolveVanityURL')) {
                $steamId = $vanities[strtolower($request['vanityurl'])] ?? null;

                return Http::response(['response' => $steamId
                    ? ['success' => 1, 'steamid' => $steamId]
                    : ['success' => 42, 'message' => 'No match']]);
            }

            $players = collect(explode(',', $request['steamids']))
                ->filter(fn (string $id) => isset($personas[$id]))
                ->map(fn (string $id) => ['steamid' => $id, 'personaname' => $personas[$id], 'profileurl' => ''])
                ->values();

            return Http::response(['response' => ['players' => $players]]);
        });
    }

    private function steamHandle(string $value): int
    {
        $member = $this->createMember();
        $member->handles()->attach($this->steam->id, ['value' => $value]);

        return $member->memberHandles()->value('id');
    }

    private function assertHandleValue(int $id, string $value): void
    {
        $this->assertDatabaseHas('handle_member', ['id' => $id, 'value' => $value]);
    }
}
