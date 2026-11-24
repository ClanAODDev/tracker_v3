<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurgeSiteDivisionCache implements ShouldQueue
{
    use Queueable;

    public const SIGNATURE_HEADER = 'X-Tracker-Signature';

    public int $tries = 3;

    public array $backoff = [10, 60];

    public function __construct(public readonly string $slug) {}

    public function handle(): void
    {
        $url    = config('services.aod_site.division_webhook_url');
        $secret = config('services.aod_site.webhook_secret');

        if (blank($url) || blank($secret)) {
            return;
        }

        $body = json_encode(['slug' => $this->slug]);

        $response = Http::timeout(5)
            ->withHeaders([self::SIGNATURE_HEADER => 'sha256=' . hash_hmac('sha256', $body, $secret)])
            ->withBody($body, 'application/json')
            ->post($url);

        if ($response->serverError()) {
            $response->throw();
        }

        if ($response->clientError()) {
            Log::warning('Site rejected the division cache purge', ['slug' => $this->slug, 'status' => $response->status()]);
        }
    }
}
