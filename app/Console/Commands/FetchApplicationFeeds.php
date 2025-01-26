<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Notifications\NewDivisionApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class FetchApplicationFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:fetch-application-feeds';

    protected $description = 'Fetch recruitment RSS feeds and notify about new applications';

    public function handle(): int
    {
        $excludedDivisions = [
            'Bluntz\' Reserves',
            'Floater',
        ];

        $divisions = Division::active()->whereNotIn('name', $excludedDivisions)->get();

        foreach ($divisions as $division) {

            try {
                $feedUrl = $division->settings()->get('recruitment_rss_feed');

                if (! $feedUrl) {
                    \Log::info('No feed URL found. Continuing...');

                    continue;
                }

                $rssContent = $this->fetchRssContent($feedUrl);

                if (! $rssContent) {
                    \Log::error("Failed to fetch RSS content for division: {$division->name}");

                    continue;
                }

                $cacheKey = "rss_feed_{$division->id}";
                $newItems = $this->detectNewItems($cacheKey, $rssContent);

                foreach ($newItems as $item) {
                    $division->notify(new NewDivisionApplication($item->title, $item->link));
                }
            } catch (\Exception $exception) {
                \Log::error($exception->getMessage());
            }
        }

        return self::SUCCESS;
    }

    public function fetchRssContent(string $url): SimpleXMLElement|false
    {
        try {
            $response = Http::get($url);

            if ($response->ok()) {
                return new SimpleXMLElement($response->body());
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

        return false;
    }

    public function detectNewItems(string $cacheKey, SimpleXMLElement $rssContent): array
    {
        $cachedGuids = Cache::get($cacheKey, []);

        $currentGuids = [];
        $newItems = [];

        foreach ($rssContent->channel->item as $item) {
            $guid = (string) $item->guid;

            $currentGuids[] = $guid;

            if (! in_array($guid, $cachedGuids)) {
                $newItems[] = $item;
            }
        }

        Cache::put($cacheKey, $currentGuids, now()->addDays(30));

        return $newItems;
    }
}
