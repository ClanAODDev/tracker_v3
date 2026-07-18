protected function calculateVoicePercent(int $memberCount, int $weeklyVoice): int
    {
        return $memberCount > 0 ? round(($weeklyVoice / $memberCount) * 100) : 0;
    }
protected function getWeeklyVoiceCount(?Census $latestCensus): int
    {
        return $latestCensus?->weekly_voice_count ?? 0;
    }
protected function getMemberCount(?Census $latestCensus, Division $division): ?int
    {
        return $latestCensus?->count ?? $division->members()->count();
    }
protected function getRecruitHistoryDate(): IlluminateSupportCarbon
    {
        return now()->subDays(98);
    }
protected function getRecruitThresholdDate(): IlluminateSupportCarbon
    {
        return now()->subDays(30);
    }
protected const WEEKSAGO_MAX = 13;
protected function getPopulationTrendColor(?int $current, ?int $previous): string
    {
        return $this->getTrendColor($current, $previous);
    }
protected function getPopulationTrendIcon(?int $current, ?int $previous): string
    {
        return $this->getTrendIcon($current, $previous);
    }
protected function getPopulationTrendDescription(?int $current, ?int $previous): string
    {
        return $this->getTrendDescription($current, $previous);
    }
protected const VOICE_THRESHOLD_SUCCESS = 30;
protected const VOICE_THRESHOLD_WARNING = 15;
<?php

namespace App\Filament\Mod\Widgets;

use App\Models\Census;
use App\Models\Division;
use App\Models\DivisionTag;
use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DivisionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $division = $this->getDivision();

        if (! $division) {
            return [
                Stat::make('No Division', 'You are not assigned to a division'),
            ];
        }

        $latestCensus = Census::where('division_id', $division->id)
            ->latest()
            ->first();

        $previousCensus = Census::where('division_id', $division->id)
            ->latest()
            ->skip(1)
            ->first();

        $memberCount = $this->getMemberCount($latestCensus, $division);
        $weeklyVoice = $this->getWeeklyVoiceCount($latestCensus);

        $voicePercent = $this->calculateVoicePercent($memberCount, $weeklyVoice);

        $tagCount          = DivisionTag::forDivision($division->id)->count();
        $taggedMemberCount = $division->members()->whereHas('tags')->count();

        $populationTrend = $this->getPopulationTrend($division);
        $voiceTrend      = $this->getVoiceTrend($division);
        $recruitsTrend   = $this->getRecruitsTrend($division);

        $recruitsThisMonth = Member::where('division_id', $division->id)
            ->where('join_date', '>=', $this->getRecruitThresholdDate())
            ->count();

        return [
            Stat::make('Total Members', number_format($memberCount))
                ->description($this->getPopulationTrendDescription($latestCensus?->count, $previousCensus?->count))
                ->descriptionIcon($this->getPopulationTrendIcon($latestCensus?->count, $previousCensus?->count))
                ->chart($populationTrend)
                ->color($this->getPopulationTrendColor($latestCensus?->count, $previousCensus?->count)),

            Stat::make('Weekly Voice', number_format($weeklyVoice))
                ->description("{$voicePercent}% of division")
                ->descriptionIcon('heroicon-m-speaker-wave')
                ->chart($voiceTrend)
                ->color($this->getVoiceColor($voicePercent)),

            Stat::make('Recruits (30d)', number_format($recruitsThisMonth))
                ->description('New members')
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart($recruitsTrend)
                ->color('info'),

            Stat::make('Tags in Use', $tagCount)
                ->description("{$taggedMemberCount} members tagged")
                ->descriptionIcon('heroicon-m-tag')
                ->color('gray'),
        ];
    }

    protected function getDivision(): ?Division
    {
        return Auth::user()?->division;
    }

    protected function getPopulationTrend(Division $division): array
    {
        return Census::where('division_id', $division->id)
            ->latest()
            ->take(14)
            ->pluck('count')
            ->reverse()
            ->values()
            ->toArray();
    }

    protected function getVoiceTrend(Division $division): array
    {
        return Census::where('division_id', $division->id)
            ->latest()
            ->take(14)
            ->pluck('weekly_voice_count')
            ->reverse()
            ->values()
            ->toArray();
    }

    protected function getRecruitsTrend(Division $division): array
    {
        $results = Member::where('division_id', $division->id)
            ->where('join_date', '>=', $this->getRecruitHistoryDate())
            ->selectRaw('FLOOR(DATEDIFF(CURDATE(), DATE(join_date)) / 7) as week_ago, COUNT(*) as count')
            ->groupBy('week_ago')
            ->pluck('count', 'week_ago');

        $data = [];
        for ($i = self::WEEKSAGO_MAX; $i >= 0; $i--) {
            $data[] = (int) ($results[$i] ?? 0);
        }

        return $data;
    }

    protected function getTrendDescription(?int $current, ?int $previous): string
    {
        if ($current === null || $previous === null) {
            return 'No previous data';
        }

        $diff = $current - $previous;

        if ($diff === 0) {
            return 'No change';
        }

        return ($diff > 0 ? '+' : '') . $diff . ' from last census';
    }

    protected function getTrendIcon(?int $current, ?int $previous): string
    {
        if ($current === null || $previous === null) {
            return 'heroicon-m-minus';
        }

        $diff = $current - $previous;

        if ($diff > 0) {
            return 'heroicon-m-arrow-trending-up';
        } elseif ($diff < 0) {
            return 'heroicon-m-arrow-trending-down';
        }

        return 'heroicon-m-minus';
    }

    protected function getTrendColor(?int $current, ?int $previous): string
    {
        if ($current === null || $previous === null) {
            return 'gray';
        }

        $diff = $current - $previous;

        if ($diff > 0) {
            return 'success';
        } elseif ($diff < 0) {
            return 'danger';
        }

        return 'gray';
    }
}
