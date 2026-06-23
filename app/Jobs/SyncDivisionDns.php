<?php

namespace App\Jobs;

use App\Models\Division;
use App\Services\CloudflareDnsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncDivisionDns implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly bool $dryRun = false) {}

    public function handle(CloudflareDnsService $service): array
    {
        $prefix    = $this->dryRun ? '[DRY RUN] ' : '';
        $expected  = static::expectedSubdomains();
        $existing  = $service->listCnames();
        $protected = collect(CloudflareDnsService::PROTECTED_SUBDOMAINS);

        $created = [];
        $deleted = [];

        foreach ($expected as $subdomain) {
            if (! $existing->has($subdomain)) {
                if (! $this->dryRun) {
                    $service->createCname($subdomain);
                }
                $created[] = $subdomain;
            }
        }

        foreach ($existing as $subdomain => $record) {
            if (! $expected->contains($subdomain) && ! $protected->contains($subdomain)) {
                if (! $this->dryRun) {
                    $service->deleteCname($record['id']);
                }
                $deleted[] = $subdomain;
            }
        }

        try {
            Log::info("{$prefix}DNS sync complete", compact('created', 'deleted'));
        } catch (Throwable) {
        }

        return compact('created', 'deleted');
    }

    public static function expectedSubdomains(): Collection
    {
        return Division::where('active', true)
            ->whereNull('shutdown_at')
            ->whereNull('deleted_at')
            ->whereNotIn('slug', CloudflareDnsService::DNS_EXCLUDED_SLUGS)
            ->get()
            ->map(fn ($d) => $d->dns_subdomain ?? $d->slug)
            ->filter()
            ->unique()
            ->values();
    }
}
