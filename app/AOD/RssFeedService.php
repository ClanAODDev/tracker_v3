<?php

namespace App\AOD;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class RssFeedService
{
    public function fetchRssContent(string $url): ?SimpleXMLElement
    {
        try {
            $response = Http::get($url);

            if ($response->ok()) {
                return new SimpleXMLElement($response->body());
            }
        } catch (\Exception $e) {
            // Handle logging or error reporting
        }

        return null;
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

        Cache::forever($cacheKey, $currentGuids);

        return $newItems;
    }
}
