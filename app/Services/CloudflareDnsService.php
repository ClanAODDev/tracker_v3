<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class CloudflareDnsService
{
    public const PROTECTED_SUBDOMAINS = [
        'www', 'ps2', 'test', 'tracker', 'tracker-dev', 'vb5', 'nextcloud', 'monitorss',
    ];

    private const CNAME_TARGET = 'clanaod.net';

    private const API_BASE = 'https://api.cloudflare.com/client/v4';

    private Client $http;

    private string $zoneId;

    private string $zoneDomain;

    public function __construct()
    {
        $this->zoneId     = config('services.cloudflare.zone_id');
        $this->zoneDomain = config('services.cloudflare.zone_domain');
        $this->http       = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.cloudflare.api_token'),
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    public function listCnames(): Collection
    {
        $response = $this->http->get(self::API_BASE . "/zones/{$this->zoneId}/dns_records", [
            'query' => ['type' => 'CNAME', 'per_page' => 100],
        ]);

        $records = json_decode($response->getBody()->getContents(), true)['result'] ?? [];

        return collect($records)
            ->filter(fn ($r) => str_ends_with($r['name'], '.' . $this->zoneDomain))
            ->keyBy(fn ($r) => str_replace('.' . $this->zoneDomain, '', $r['name']));
    }

    public function createCname(string $subdomain): void
    {
        $this->http->post(self::API_BASE . "/zones/{$this->zoneId}/dns_records", [
            'json' => [
                'type'    => 'CNAME',
                'name'    => $subdomain . '.' . $this->zoneDomain,
                'content' => self::CNAME_TARGET,
                'proxied' => true,
                'ttl'     => 1,
            ],
        ]);
    }

    public function deleteCname(string $recordId): void
    {
        $this->http->delete(self::API_BASE . "/zones/{$this->zoneId}/dns_records/{$recordId}");
    }
}
