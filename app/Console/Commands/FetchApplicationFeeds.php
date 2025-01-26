<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Notifications\NewDivisionApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class FetchApplicationFeeds extends Command
{
    /**
     * Execute the console command.
     */
    protected $signature = 'do:fetch-application-feeds {--notify}';

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

        $this->pruneOldItems();

        return self::SUCCESS;
    }

    protected function fetchRssContent(string $url): SimpleXMLElement|false
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

    protected function processRssFeed(Division $division, SimpleXMLElement $rssContent): void
    {
        foreach ($rssContent->channel->item as $item) {
            $threadId = str_replace('https://www.clanaod.net/forums/showthread.php?t=', '', $item->guid);

            if (DB::table('application_items')->where('guid', $threadId)->exists()) {
                continue;
            }

            DB::table('application_items')->insert([
                'guid' => $threadId,
                'pub_date' => \Carbon\Carbon::createFromTimeString($item->pubDate),
            ]);

            if ($this->option('notify')) {
                $division->notify(new NewDivisionApplication((string) $item->title, (string) $item->link));
            }
        }
    }

    protected function pruneOldItems(): void
    {
        DB::table('application_items')
            ->where('pub_date', '<', now()->subDays(45))
            ->delete();
    }
}
