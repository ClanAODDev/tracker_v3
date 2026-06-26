<?php

namespace App\Jobs;

use App\Models\Division;
use App\Notifications\Channel\NotifyAdminDNSChange;
use App\Services\CloudflareDnsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SyncDivisionDns implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly bool $dryRun = false) {}

    public function handle(CloudflareDnsService $service): array
    {
        if (! $service->isConfigured()) {
            return ['created' => [], 'deleted' => []];
        }

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

        if (! $this->dryRun && ($created || $deleted)) {
            try {
                Log::info('DNS sync complete', compact('created', 'deleted'));
            } catch (Throwable) {
            }

            Notification::route('it_team', config('aod.it-team-channel'))
                ->notify(new NotifyAdminDNSChange($created, $deleted, $service->zoneDomain));
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
            ->map(fn ($d) => $d->settings()->get('dns_subdomain', null) ?? $d->slug)
            ->filter()
            ->unique()
            ->values();
    }
}
