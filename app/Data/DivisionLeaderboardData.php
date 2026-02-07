<?php

namespace App\Data;

use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

readonly class DivisionLeaderboardData
{
    private const CACHE_KEY = 'division_leaderboard';

    private const CACHE_TTL = 3600;

    public function __construct(
        public Collection $voiceLeaders,
        public Collection $growthLeaders,
        public Collection $recruitLeaders,
        public ?int $userDivisionId,
    ) {}

    public static function forUser(User $user): self
    {
        $userDivisionId = $user->member->division_id;
        $cached = self::getCachedLeaderboards();

        return new self(
            voiceLeaders: $cached['voiceLeaders'],
            growthLeaders: $cached['growthLeaders'],
            recruitLeaders: $cached['recruitLeaders'],
            userDivisionId: $userDivisionId,
        );
    }

    private static function getCachedLeaderboards(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $divisions = Division::query()
                ->active()
                ->withoutFloaters()
                ->withoutBR()
                ->whereHas('members')
                ->withCount('members')
                ->withCount(['members as recruits_count' => function ($query) {
                    $query->where('join_date', '>=', now()->startOfMonth());
                }])
                ->with(['census' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(8);
                }])
                ->get();

            return [
                'voiceLeaders' => self::calculateVoiceLeaders($divisions),
                'growthLeaders' => self::calculateGrowthLeaders($divisions),
                'recruitLeaders' => self::calculateRecruitLeaders($divisions),
            ];
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function calculateVoiceLeaders(Collection $divisions): Collection
    {
        return $divisions
            ->map(function (Division $division) {
                $censusRecords = $division->census->sortBy('created_at')->values();
                $latest = $censusRecords->last();
                $previous = $censusRecords->count() > 1 ? $censusRecords->get($censusRecords->count() - 2) : null;

                $voiceRate = 0;
                if ($latest && $latest->count > 0) {
                    $voiceRate = round(($latest->weekly_voice_count / $latest->count) * 100);
                }

                $previousVoiceRate = 0;
                if ($previous && $previous->count > 0) {
                    $previousVoiceRate = round(($previous->weekly_voice_count / $previous->count) * 100);
                }

                $trend = $censusRecords->map(function ($c) {
                    return $c->count > 0 ? round(($c->weekly_voice_count / $c->count) * 100) : 0;
                })->values()->toArray();

                return [
                    'id' => $division->id,
                    'name' => $division->name,
                    'slug' => $division->slug,
                    'logo' => self::getDivisionLogo($division),
                    'value' => (int) $voiceRate,
                    'formatted' => $voiceRate . '%',
                    'trend' => $trend,
                    'trending' => $voiceRate >= $previousVoiceRate ? 'up' : 'down',
                ];
            })
            ->sortByDesc('value')
            ->values();
    }

    private static function calculateGrowthLeaders(Collection $divisions): Collection
    {
        return $divisions
            ->map(function (Division $division) {
                $censusRecords = $division->census->sortBy('created_at')->values();
                $currentCount = $division->members_count;
                $previousCount = $censusRecords->last()?->count ?? 0;

                $growthRate = 0;
                if ($previousCount > 0 && $currentCount > 0) {
                    $rawChange = (1 - $previousCount / $currentCount) * 100;
                    $growthRate = round($rawChange, 1);
                }

                $trend = $censusRecords->pluck('count')->values()->toArray();
                $trend[] = $currentCount;

                return [
                    'id' => $division->id,
                    'name' => $division->name,
                    'slug' => $division->slug,
                    'logo' => self::getDivisionLogo($division),
                    'value' => $growthRate,
                    'formatted' => ($growthRate >= 0 ? '+' : '') . $growthRate . '%',
                    'trend' => $trend,
                ];
            })
            ->sortByDesc('value')
            ->values();
    }

    private static function calculateRecruitLeaders(Collection $divisions): Collection
    {
        return $divisions
            ->map(function (Division $division) {
                return [
                    'id' => $division->id,
                    'name' => $division->name,
                    'slug' => $division->slug,
                    'logo' => self::getDivisionLogo($division),
                    'value' => $division->recruits_count,
                    'formatted' => $division->recruits_count,
                ];
            })
            ->sortByDesc('value')
            ->values();
    }

    private static function getDivisionLogo(Division $division): ?string
    {
        if ($division->logo && Storage::disk('public')->exists($division->logo)) {
            return asset(Storage::url($division->logo));
        }

        return null;
    }
}
