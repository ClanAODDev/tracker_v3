<?php

use App\Models\Member;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('members/{member}-{slug}/my-awards.png', function (Member $member, \App\AOD\MemberAwardImage $service) {
    return handleImageGeneration($member, 'member_awards', function ($member) use ($service) {
        return $service->generateAwardsImage($member);
    });
})->where('member', '[0-9]+');

Route::get('members/{member}/my-awards.png', function (Member $member, \App\AOD\MemberAwardImage $service) {
    return handleImageGeneration($member, 'member_awards', function ($member) use ($service) {
        return $service->generateAwardsImage($member);
    });
})->where('member', '[0-9]+');

Route::get('members/{member}-{slug}/my-awards-cluster.png', function (Member $member, \App\AOD\MemberAwardCluster $service) {
    return handleImageGeneration($member, 'member_awards_cluster', function ($member) use ($service) {
        return $service->generateClusterImage($member);
    });
})->where('member', '[0-9]+');

Route::get('members/{member}/my-awards-cluster.png', function (Member $member, \App\AOD\MemberAwardCluster $service) {
    return handleImageGeneration($member, 'member_awards_cluster', function ($member) use ($service) {
        return $service->generateClusterImage($member);
    });
})->where('member', '[0-9]+');

function handleImageGeneration(Member $member, string $cachePrefix, callable $generateImageCallback)
{
    $cacheKey = sprintf(
        '%s_%s_%s',
        $cachePrefix,
        $member->id,
        md5(json_encode(request()->except(['debug'])))
    );

    $cacheMins = config('aod.awards.cache_minutes', 15);

    return Cache::remember(
        key: $cacheKey,
        ttl: now()->addMinutes($cacheMins),
        callback: function () use ($member, $generateImageCallback) {
            $imageContent = $generateImageCallback($member);

            return response($imageContent)->header('Content-Type', 'image/png');
        });
}
