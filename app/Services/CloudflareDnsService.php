<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CloudflareDnsService
{
    public const PROTECTED_SUBDOMAINS = [
        'www', 'ps2', 'test', 'tracker', 'tracker-dev', 'vb5', 'nextcloud', 'monitorss',
    ];

    private const CNAME_TARGET = 'clanaod.net';

    private const API_BASE = 'https://api.cloudflare.com/client/v4';

    private string $zoneId;

    private string $zoneDomain;

    public function __construct()
    {
        $this->zoneId     = config('services.cloudflare.zone_id');
        $this->zoneDomain = config('services.cloudflare.zone_domain');
    }

    public function listCnames(): Collection
    {
        $response = Http::withToken(config('services.cloudflare.api_token'))
            ->get(self::API_BASE . "/zones/{$this->zoneId}/dns_records", [
                'type'     => 'CNAME',
                'per_page' => 100,
            ]);

        $records = $response->json('result', []);

        return collect($records)
            ->filter(fn ($r) => str_ends_with($r['name'], '.' . $this->zoneDomain))
            ->keyBy(fn ($r) => str_replace('.' . $this->zoneDomain, '', $r['name']));
    }

    public function createCname(string $subdomain): void
    {
        Http::withToken(config('services.cloudflare.api_token'))
            ->post(self::API_BASE . "/zones/{$this->zoneId}/dns_records", [
                'type'    => 'CNAME',
                'name'    => $subdomain . '.' . $this->zoneDomain,
                'content' => self::CNAME_TARGET,
                'proxied' => true,
                'ttl'     => 1,
            ]);
    }

    public function deleteCname(string $recordId): void
    {
        Http::withToken(config('services.cloudflare.api_token'))
            ->delete(self::API_BASE . "/zones/{$this->zoneId}/dns_records/{$recordId}");
    }
}
