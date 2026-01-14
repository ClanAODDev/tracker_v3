<?php

use App\AOD\MemberAwardCluster;
use App\AOD\MemberAwardImage;
use App\Models\Member;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('members/{identifier}/my-awards.png', function (string $identifier, MemberAwardImage $service) {
    return handleAwardImage($identifier, 'member_awards', fn ($m) => $service->generateAwardsImagePng($m, withBackground: true));
})->where('identifier', '[0-9]+(-[^/]+)?');

Route::get('members/{identifier}/my-awards-transparent.png', function (string $identifier, MemberAwardImage $service) {
    return handleAwardImage($identifier, 'member_awards_transparent', fn ($m) => $service->generateAwardsImagePng($m, withBackground: false));
})->where('identifier', '[0-9]+(-[^/]+)?');

Route::get('members/{identifier}/my-awards-cluster.png', function (string $identifier, MemberAwardCluster $service) {
    return handleAwardImage($identifier, 'member_awards_cluster', fn ($m) => $service->generateClusterImage($m));
})->where('identifier', '[0-9]+(-[^/]+)?');

function handleAwardImage(string $identifier, string $cachePrefix, callable $generateCallback)
{
    $memberId = (int) explode('-', $identifier)[0];
    $member = Member::where('clan_id', $memberId)->firstOrFail();

    $generateResponse = fn () => response($generateCallback($member))->header('Content-Type', 'image/png');

    if (app()->environment('local')) {
        return $generateResponse();
    }

    $cacheKey = sprintf(
        '%s_%s_%s',
        $cachePrefix,
        $member->id,
        md5(json_encode(request()->except(['debug'])))
    );

    return Cache::remember($cacheKey, now()->addMinutes(config('aod.awards.cache_minutes', 15)), $generateResponse);
}
