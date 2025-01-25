<?php

namespace App\Console\Commands;

use App\AOD\RssFeedService;
use App\Models\Division;
use App\Notifications\NewDivisionApplication;
use Illuminate\Console\Command;

class FetchApplicationFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:fetch-application-feeds';

    protected $description = 'Fetch recruitment RSS feeds and notify about new applications';

    public function handle()
    {
        $excludedDivisions = [
            'Bluntz\' Reserves',
            'Floater',
        ];

        $divisions = Division::active()->whereNotIn('name', $excludedDivisions)->get();

        $rssFeedService = new RssFeedService;

        foreach ($divisions as $division) {
            \Log::info("Checking {$division->name} for new threads");

            try {
                $feedUrl = $division->settings()->get('recruitment_rss_feed');

                if (! $feedUrl) {
                    \Log::info('No feed URL found. Continuing...');

                    continue;
                }

                $rssContent = $rssFeedService->fetchRssContent($feedUrl);

                if (! $rssContent) {
                    \Log::error("Failed to fetch RSS content for division: {$division->name}");

                    continue;
                }

                $cacheKey = "rss_feed_{$division->id}";
                $newItems = $rssFeedService->detectNewItems($cacheKey, $rssContent);

                foreach ($newItems as $item) {
                    $division->notify(new NewDivisionApplication($item->title, $item->link));
                }
            } catch (\Exception $exception) {
                \Log::error($exception->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
