<?php

namespace App\Jobs;

use App\Models\Division;
use App\Services\CloudflareDnsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SyncDivisionDns implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly bool $dryRun = false) {}

    public function handle(CloudflareDnsService $service): void
    {
        $prefix   = $this->dryRun ? '[DRY RUN] ' : '';
        $expected = static::expectedSubdomains();
        $existing = $service->listCnames();
        $protected = collect(CloudflareDnsService::PROTECTED_SUBDOMAINS);

        $created = [];
        $deleted = [];

        foreach ($expected as $subdomain) {
            if (! $existing->has($subdomain)) {
                if (! $this->dryRun) {
                    $service->createCname($subdomain);
                }
                $created[] = $subdomain;
                Log::info("{$prefix}DNS sync: create CNAME {$subdomain}");
            }
        }

        foreach ($existing as $subdomain => $record) {
            if (! $expected->contains($subdomain) && ! $protected->contains($subdomain)) {
                if (! $this->dryRun) {
                    $service->deleteCname($record['id']);
                }
                $deleted[] = $subdomain;
                Log::info("{$prefix}DNS sync: delete CNAME {$subdomain}");
            }
        }

        Log::info("{$prefix}DNS sync complete", compact('created', 'deleted'));
    }

    public static function expectedSubdomains(): Collection
    {
        return Division::whereNull('shutdown_at')
            ->whereNull('deleted_at')
            ->get()
            ->map(fn ($d) => $d->dns_subdomain ?? $d->slug)
            ->filter()
            ->unique()
            ->values();
    }
}
