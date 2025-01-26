<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Notifications\NewDivisionApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class FetchApplicationFeeds extends Command
{
    private string $useragent = 'Tracker - App Scraper';

    protected $signature = 'do:fetch-application-feeds {--notify}';

    protected $description = 'Fetch recruitment RSS feeds and notify about new applications';

    public function handle(): int
    {
        $divisions = Division::active()->whereNotIn('name', [
            'Bluntz\' Reserves',
            'Floater',
        ])->get();

        foreach ($divisions as $division) {
            try {
                $feedUrl = $division->settings()->get('recruitment_rss_feed');

                if (! $feedUrl) {
                    continue;
                }

                $rssContent = $this->fetchRssContent($feedUrl);

                if (! $rssContent) {
                    \Log::error("Failed to fetch RSS content for division: {$division->name}");

                    continue;
                }

                $this->processRssFeed($division, $rssContent);
            } catch (\Exception $exception) {
                \Log::error($exception->getMessage());
            }
        }

        return self::SUCCESS;
    }

    protected function fetchRssContent(string $url): SimpleXMLElement|false
    {
        try {
            $response = Http::withUserAgent($this->useragent)->get($url);

            if ($response->ok()) {
                return new SimpleXMLElement($response->body());
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return false;
    }

    protected function processRssFeed(Division $division, SimpleXMLElement $rssContent): void
    {
        foreach ($rssContent->channel->item as $item) {
            $threadId = null;
            if (preg_match('/\?t=(\d+)/', $item->guid, $matches)) {
                $threadId = $matches[1];
            }

            if (!$threadId) {
                // couldn't reliably extract a thread id so let's move on
                continue;
            }

            $cacheKey = "application_item_{$threadId}";

            if (Cache::has($cacheKey)) {
                continue;
            }

            Cache::put($cacheKey, [
                'guid' => $threadId,
                'pub_date' => now()->toDateTimeString(),
            ], now()->addDays(45));

            if ($this->option('notify')) {
                $division->notify(new NewDivisionApplication((string) $item->title, (string) $item->link));
            }
        }
    }
}
