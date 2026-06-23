<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CloudflareDnsService
{
    public const PROTECTED_SUBDOMAINS = [
        'www', 'ps2', 'test', 'tracker', 'tracker-dev', 'vb5', 'nextcloud', 'monitorss',
    ];

    public const DNS_EXCLUDED_SLUGS = ['floater', 'bluntz-reserves'];

    private const CNAME_TARGET = 'clanaod.net';

    private const API_BASE = 'https://api.cloudflare.com/client/v4';

    private string $zoneId;

    public string $zoneDomain;

    public function __construct()
    {
        $this->zoneId     = config('services.cloudflare.zone_id');
        $this->zoneDomain = config('services.cloudflare.zone_domain');
    }

    public function listCnames(): Collection
    {
        $records = [];
        $page    = 1;

        do {
            $response = $this->http()
                ->get(self::API_BASE . "/zones/{$this->zoneId}/dns_records", [
                    'type'     => 'CNAME',
                    'per_page' => 100,
                    'page'     => $page,
                ])
                ->throw();

            $body       = $response->json();
            $records    = array_merge($records, $body['result'] ?? []);
            $totalPages = $body['result_info']['total_pages'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        return collect($records)
            ->filter(fn ($r) => str_ends_with($r['name'], '.' . $this->zoneDomain))
            ->keyBy(fn ($r) => str_replace('.' . $this->zoneDomain, '', $r['name']));
    }

    public function createCname(string $subdomain): void
    {
        $this->http()->post(self::API_BASE . "/zones/{$this->zoneId}/dns_records", [
            'type'    => 'CNAME',
            'name'    => $subdomain . '.' . $this->zoneDomain,
            'content' => self::CNAME_TARGET,
            'proxied' => true,
            'ttl'     => 1,
        ]);
    }

    public function deleteCname(string $recordId): void
    {
        $this->http()->delete(self::API_BASE . "/zones/{$this->zoneId}/dns_records/{$recordId}");
    }

    private function http(): PendingRequest
    {
        $request = Http::withToken(config('services.cloudflare.api_key'));

        $proxy = config('services.cloudflare.proxy');
        if ($proxy) {
            $request = $request->withOptions(['proxy' => $proxy]);
        }

        return $request;
    }
}
