<?php

namespace App\AOD;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class RssFeedService
{
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
