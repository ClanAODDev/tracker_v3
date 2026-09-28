<?php

namespace Tests\Unit\Support;

use App\Enums\SteamInputFormat;
use App\Support\Steam\SteamIdParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SteamIdParserTest extends TestCase
{
    public static function offlineConversions(): array
    {
        return [
            'bare id64'                 => ['76561197960311641', SteamInputFormat::ID64, '76561197960311641'],
            'id64 with whitespace'      => ["  76561197960311641\n", SteamInputFormat::ID64, '76561197960311641'],
            'id64 with trailing slash'  => ['76561197960517833/', SteamInputFormat::ID64, '76561197960517833'],
            'id64 with leading slash'   => ['/76561198096709098/', SteamInputFormat::ID64, '76561198096709098'],
            'id64 with trailing equals' => ['76561198020387906=', SteamInputFormat::ID64, '76561198020387906'],
            'http profiles url'         => ['http://steamcommunity.com/profiles/76561197968443902', SteamInputFormat::PROFILE_URL, '76561197968443902'],
            'profiles url with extras'  => ['https://www.steamcommunity.com/profiles/76561197968443902/games/?tab=all', SteamInputFormat::PROFILE_URL, '76561197968443902'],
            'schemeless profiles url'   => ['steamcommunity.com/profiles/76561197968443902', SteamInputFormat::PROFILE_URL, '76561197968443902'],
            'legacy steam id'           => ['STEAM_0:1:12345', SteamInputFormat::LEGACY, '76561197960290419'],
            'steam id3'                 => ['[U:1:12345]', SteamInputFormat::STEAM_ID3, '76561197960278073'],
            'friend code'               => ['30555111', SteamInputFormat::FRIEND_CODE, '76561197990820839'],
            'zero padded friend code'   => ['00221114456', SteamInputFormat::FRIEND_CODE, '76561198181380184'],
            'invite link'               => ['https://s.team/p/hkt-qmmr/jppntmkc', SteamInputFormat::INVITE_LINK, '76561198052391052'],
        ];
    }

    #[Test]
    #[DataProvider('offlineConversions')]
    public function it_converts_known_formats_without_the_api(string $input, SteamInputFormat $format, string $steamId): void
    {
        $parsed = SteamIdParser::parse($input);

        $this->assertSame($format, $parsed->format);
        $this->assertSame($steamId, $parsed->steamId);
        $this->assertFalse($parsed->needsLookup());
    }

    public static function vanities(): array
    {
        return [
            'bare name'              => ['_Blinker', '_Blinker'],
            'id url'                 => ['http://steamcommunity.com/id/AcedPentaKill/', 'AcedPentaKill'],
            'id url with extra path' => ['https://steamcommunity.com/id/Woolnut/friends/', 'Woolnut'],
            'numeric id url'         => ['https://steamcommunity.com/id/83350/', '83350'],
            'slashed name'           => ['/malacath/', 'malacath'],
        ];
    }

    #[Test]
    #[DataProvider('vanities')]
    public function it_extracts_custom_url_names_for_lookup(string $input, string $vanity): void
    {
        $parsed = SteamIdParser::parse($input);

        $this->assertTrue($parsed->needsLookup());
        $this->assertSame($vanity, $parsed->vanity);
        $this->assertNull($parsed->steamId);
    }

    public static function invalidInputs(): array
    {
        return [
            'blank'                      => ['   ', 'Empty value'],
            'truncated url'              => ['https://steamcommunity.com/profiles/...47098/FlameFoe', 'Value was truncated'],
            'id64 missing a digit'       => ['7656119810317823', 'SteamID64 is missing digits'],
            'profiles url missing digit' => ['https://steamcommunity.com/profiles/7656119810317823', 'SteamID64 is missing digits'],
            'out of range 17 digits'     => ['48656015677718650', 'Not a valid SteamID64'],
            'too long for friend code'   => ['316452798643262', 'Number is not a SteamID64 or friend code'],
            'friend code over 32 bits'   => ['5000000000', 'Number is not a SteamID64 or friend code'],
            'other site'                 => ['https://psnprofiles.com/EL_JEFE_V', 'Not a Steam profile URL'],
            'display name with spaces'   => ['-=312th=- Cowboy', 'Not a SteamID or custom URL name'],
            'clan tag brackets'          => ['[AOD]smores1724', 'Not a SteamID or custom URL name'],
            'battle tag'                 => ['JUSTSOM3PLAY3R#7102', 'Not a SteamID or custom URL name'],
            'dotted name'                => ['terranova.s.a.2020', 'Not a SteamID or custom URL name'],
        ];
    }

    #[Test]
    #[DataProvider('invalidInputs')]
    public function it_rejects_values_that_cannot_be_a_steam_account(string $input, string $reason): void
    {
        $parsed = SteamIdParser::parse($input);

        $this->assertTrue($parsed->isInvalid());
        $this->assertSame($reason, $parsed->reason);
    }

    #[Test]
    public function it_validates_the_steamid64_range(): void
    {
        $this->assertTrue(SteamIdParser::isValidId64('76561197960265729'));
        $this->assertFalse(SteamIdParser::isValidId64('76561197960265728'));
        $this->assertFalse(SteamIdParser::isValidId64('7656119796026572'));
        $this->assertFalse(SteamIdParser::isValidId64('76561197960265729/'));
    }
}
