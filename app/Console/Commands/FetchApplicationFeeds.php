<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Notifications\Channel\NotifyDivisionNewApplication;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;

class FetchApplicationFeeds extends BaseCommand
{
    protected $signature = 'tracker:fetch-applications
                            {--notify : Send notifications for new applications}
                            {--fresh : Clear cached items and process all feeds as new}';

    protected $description = 'Fetch recruitment RSS feeds and notify about new applications';

    protected string $userAgent = 'Tracker - App Scraper';

    protected array $stats = [
        'divisions_processed' => 0,
        'applications_found' => 0,
        'notifications_sent' => 0,
        'errors' => 0,
    ];

    public function handle(): int
    {
        $divisions = $this->getDivisionsToProcess();

        if ($divisions->isEmpty()) {
            $this->info('No divisions with RSS feeds to process.');

            return self::SUCCESS;
        }

        foreach ($divisions as $division) {
            $this->processDivision($division);
        }

        $this->logStats();

        return self::SUCCESS;
    }

    protected function getDivisionsToProcess()
    {
        $excludedDivisions = config('tracker.excluded_divisions', []);

        return Division::active()
            ->whereNotIn('name', $excludedDivisions)
            ->get()
            ->filter(fn ($d) => $d->settings()->get('recruitment_rss_feed'));
    }

    protected function processDivision(Division $division): void
    {
        $this->verbose("Processing {$division->name}...");

        try {
            $feedUrl = $division->settings()->get('recruitment_rss_feed');
            $rssContent = $this->fetchRssContent($feedUrl);

            if (! $rssContent) {
                $this->stats['errors']++;

                return;
            }

            $this->processRssFeed($division, $rssContent);
            $this->stats['divisions_processed']++;
        } catch (Exception $exception) {
            $this->logError("Error processing division {$division->name}", $exception);
            $this->stats['errors']++;
        }
    }

    protected function fetchRssContent(string $url): SimpleXMLElement|false
    {
        try {
            $response = Http::withUserAgent($this->userAgent)
                ->timeout(config('tracker.rss_timeout', 10))
                ->retry(2, 100)
                ->get($url);

            if (! $response->ok()) {
                $this->logError("RSS fetch returned status {$response->status()} for {$url}");

                return false;
            }

            $xml = new SimpleXMLElement($response->body());

            if (! $this->isValidRssFeed($xml)) {
                $this->logError("Invalid RSS feed structure for {$url}");

                return false;
            }

            return $xml;
        } catch (Exception $exception) {
            $this->logError("Failed to fetch RSS from {$url}", $exception);
        }

        return false;
    }

    protected function isValidRssFeed(SimpleXMLElement $xml): bool
    {
        return isset($xml->channel) && isset($xml->channel->item);
    }

    protected function processRssFeed(Division $division, SimpleXMLElement $rssContent): void
    {
        foreach ($rssContent->channel->item as $item) {
            $threadId = $this->extractThreadId((string) $item->guid);

            if (! $threadId) {
                continue;
            }

            $cacheKey = "application_item_{$threadId}";

            if ($this->option('fresh')) {
                Cache::forget($cacheKey);
            }

            if (Cache::has($cacheKey)) {
                continue;
            }

            $this->cacheApplication($cacheKey, $threadId);
            $this->stats['applications_found']++;

            $this->verbose("  New application: {$item->title}");

            if ($this->option('notify')) {
                $link = $this->normalizeUrl((string) $item->link);
                $division->notify(new NotifyDivisionNewApplication((string) $item->title, $link));
                $this->stats['notifications_sent']++;
            }
        }
    }

    protected function cacheApplication(string $cacheKey, string $threadId): void
    {
        $ttlDays = config('tracker.application_cache_days', 45);

        Cache::put($cacheKey, [
            'guid' => $threadId,
            'pub_date' => now()->toDateTimeString(),
        ], now()->addDays($ttlDays));
    }

    protected function extractThreadId(string $guid): ?string
    {
        if (preg_match('/\?t=(\d+)/', $guid, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function normalizeUrl(string $url): string
    {
        return Str::of($url)
            ->when(
                fn ($v) => ! Str::startsWith($v, ['http://', 'https://']),
                fn ($v) => Str::startsWith($v, '//')
                    ? $v->prepend('https:')
                    : $v->prepend('https://')->ltrim('/')
            )
            ->toString();
    }

    protected function verbose(string $message): void
    {
        if ($this->getOutput()->isVerbose()) {
            $this->line($message);
        }
    }

    protected function logStats(): void
    {
        if ($this->getOutput()->isVerbose()) {
            $this->newLine();
            $this->info('Summary:');
            $this->line("  Divisions processed: {$this->stats['divisions_processed']}");
            $this->line("  Applications found: {$this->stats['applications_found']}");
            $this->line("  Notifications sent: {$this->stats['notifications_sent']}");

            if ($this->stats['errors'] > 0) {
                $this->warn("  Errors: {$this->stats['errors']}");
            }
        }

        if ($this->stats['applications_found'] > 0 || $this->stats['errors'] > 0) {
            Log::info('Application feeds processed', $this->stats);
        }
    }
}
