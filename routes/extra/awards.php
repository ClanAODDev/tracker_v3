<?php

use App\Models\Member;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('members/{member}/my-awards.png', function (Member $member, \App\AOD\MemberAwardImage $service) {
    return handleImageGeneration($member, 'member_awards', function ($member) use ($service) {
        return $service->generateAwardsImage($member);
    });
});

Route::get('members/{member}/my-awards-cluster.png', function (Member $member, \App\AOD\MemberAwardCluster $service) {
    return handleImageGeneration($member, 'member_awards_cluster', function ($member) use ($service) {
        return $service->generateClusterImage($member);
    });
});

function handleImageGeneration(Member $member, string $cachePrefix, callable $generateImageCallback)
{
    $cacheKey = sprintf(
        '%s_%s_%s',
        $cachePrefix,
        $member->id,
        md5(json_encode(request()->except(['debug'])))
    );

    return Cache::remember($cacheKey, now()->addHour(), function () use ($member, $generateImageCallback) {
        $imageContent = $generateImageCallback($member);

        return response($imageContent)->header('Content-Type', 'image/png');
    });
}
