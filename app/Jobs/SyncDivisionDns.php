<?php

namespace App\Jobs;

use App\Models\Division;
use App\Services\CloudflareDnsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncDivisionDns implements ShouldQueue
{
    use Queueable;

    public function handle(CloudflareDnsService $service): void
    {
        $expected = Division::whereNull('shutdown_at')
            ->whereNull('deleted_at')
            ->get()
            ->map(fn ($d) => $d->dns_subdomain ?? $d->slug)
            ->filter()
            ->unique()
            ->values();

        $existing = $service->listCnames();

        $created = [];
        $deleted = [];

        foreach ($expected as $subdomain) {
            if (! $existing->has($subdomain)) {
                $service->createCname($subdomain);
                $created[] = $subdomain;
            }
        }

        $protected = collect(CloudflareDnsService::PROTECTED_SUBDOMAINS);

        foreach ($existing as $subdomain => $record) {
            if (! $expected->contains($subdomain) && ! $protected->contains($subdomain)) {
                $service->deleteCname($record['id']);
                $deleted[] = $subdomain;
            }
        }

        Log::info('DNS sync complete', [
            'created' => $created,
            'deleted' => $deleted,
        ]);
    }
}
