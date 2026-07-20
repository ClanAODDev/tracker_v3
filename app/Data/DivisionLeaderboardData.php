<?php

namespace App\Data;

use App\Models\Division;
use App\Models\LeaderboardSnapshot;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $cached         = self::getCachedLeaderboards();

        return new self(
            voiceLeaders: $cached['voiceLeaders'],
            growthLeaders: $cached['growthLeaders'],
            recruitLeaders: $cached['recruitLeaders'],
            userDivisionId: $userDivisionId,
        );
    }

    public static function calculate(): array
    {
        $divisions = Division::query()
            ->active()
            ->whereNull('shutdown_at')
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

        $recruitTrends = self::getMonthlyRecruitTrends($divisions->pluck('id'));

        return [
            'voiceLeaders'   => self::calculateVoiceLeaders($divisions),
            'growthLeaders'  => self::calculateGrowthLeaders($divisions),
            'recruitLeaders' => self::calculateRecruitLeaders($divisions, $recruitTrends),
        ];
    }

    private static function getCachedLeaderboards(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $data = self::calculate();

            return self::enrichWithMovement($data);
        });
    }

    private static function enrichWithMovement(array $data): array
    {
        $latestDate = LeaderboardSnapshot::orderByDesc('snapshot_date')
            ->value('snapshot_date');

        if (! $latestDate) {
            return $data;
        }

        $snapshots = LeaderboardSnapshot::where('snapshot_date', $latestDate)
            ->get()
            ->groupBy('category')
            ->map(fn ($rows) => $rows->keyBy('division_id'));

        $categoryMap = [
            'voiceLeaders'   => 'voice',
            'growthLeaders'  => 'growth',
            'recruitLeaders' => 'recruits',
        ];

        foreach ($categoryMap as $key => $category) {
            $categorySnapshots = $snapshots->get($category, collect());

            $data[$key] = $data[$key]->values()->map(function ($entry, $index) use ($categorySnapshots) {
                $snapshot    = $categorySnapshots->get($entry['id']);
                $currentRank = $index + 1;

                $entry['rank_change']   = $snapshot ? $snapshot->rank - $currentRank : 0;
                $entry['previous_rank'] = $snapshot?->rank;

                return $entry;
            });
        }

        return $data;
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
                $latest        = $censusRecords->last();
                $previous      = $censusRecords->count() > 1 ? $censusRecords->get($censusRecords->count() - 2) : null;

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
                    'id'        => $division->id,
                    'name'      => $division->name,
                    'slug'      => $division->slug,
                    'logo'      => self::getDivisionLogo($division),
                    'value'     => (int) $voiceRate,
                    'formatted' => $voiceRate . '%',
                    'trend'     => $trend,
                    'trending'  => $voiceRate >= $previousVoiceRate ? 'up' : 'down',
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
                $currentCount  = $division->members_count;
                $previousCount = $censusRecords->last()?->count ?? 0;

                $growthRate = 0;
                if ($previousCount > 0 && $currentCount > 0) {
                    $rawChange  = (1 - $previousCount / $currentCount) * 100;
                    $growthRate = round($rawChange, 1);
                }

                $trend   = $censusRecords->pluck('count')->values()->toArray();
                $trend[] = $currentCount;

                return [
                    'id'        => $division->id,
                    'name'      => $division->name,
                    'slug'      => $division->slug,
                    'logo'      => self::getDivisionLogo($division),
                    'value'     => $growthRate,
                    'formatted' => ($growthRate >= 0 ? '+' : '') . $growthRate . '%',
                    'trend'     => $trend,
                ];
            })
            ->sortByDesc('value')
            ->values();
    }

    private static function calculateRecruitLeaders(Collection $divisions, Collection $recruitTrends): Collection
    {
        return $divisions
            ->map(function (Division $division) use ($recruitTrends) {
                $trend    = $recruitTrends->get($division->id, []);
                $previous = count($trend) >= 2 ? $trend[count($trend) - 2] : 0;

                return [
                    'id'        => $division->id,
                    'name'      => $division->name,
                    'slug'      => $division->slug,
                    'logo'      => self::getDivisionLogo($division),
                    'value'     => $division->recruits_count,
                    'formatted' => $division->recruits_count,
                    'trend'     => $trend,
                    'trending'  => $division->recruits_count >= $previous ? 'up' : 'down',
                ];
            })
            ->sortByDesc('value')
            ->values();
    }

    private static function getMonthlyRecruitTrends(Collection $divisionIds): Collection
    {
        $months = 6;

        return Member::query()
            ->whereIn('division_id', $divisionIds)
            ->where('join_date', '>=', now()->subMonths($months)->startOfMonth())
            ->select('division_id', DB::raw("DATE_FORMAT(join_date, '%Y-%m') as month"), DB::raw('COUNT(*) as total'))
            ->groupBy('division_id', 'month')
            ->orderBy('month')
            ->get()
            ->groupBy('division_id')
            ->map(function ($rows) use ($months) {
                $lookup = $rows->pluck('total', 'month');
                $trend  = [];
                for ($i = $months - 1; $i >= 0; $i--) {
                    $key     = now()->subMonths($i)->format('Y-m');
                    $trend[] = (int) ($lookup[$key] ?? 0);
                }

                return $trend;
            });
    }

    private static function getDivisionLogo(Division $division): ?string
    {
        if ($division->logo && Storage::disk('public')->exists($division->logo)) {
            return asset(Storage::url($division->logo));
        }

        return null;
    }
}
