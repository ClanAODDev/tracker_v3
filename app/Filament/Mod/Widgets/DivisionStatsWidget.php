<?php

namespace App\Filament\Mod\Widgets;

use App\Models\Census;
use App\Models\Division;
use App\Models\DivisionTag;
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

        $memberCount = $latestCensus?->count ?? $division->members()->count();
        $weeklyVoice = $latestCensus?->weekly_voice_count ?? 0;

        $voicePercent = $memberCount > 0 ? round(($weeklyVoice / $memberCount) * 100) : 0;

        $tagCount = DivisionTag::forDivision($division->id)->count();
        $taggedMemberCount = $division->members()->whereHas('tags')->count();

        $populationTrend = $this->getPopulationTrend($division);

        return [
            Stat::make('Total Members', number_format($memberCount))
                ->description($this->getTrendDescription($latestCensus?->count, $previousCensus?->count))
                ->descriptionIcon($this->getTrendIcon($latestCensus?->count, $previousCensus?->count))
                ->chart($populationTrend)
                ->color($this->getTrendColor($latestCensus?->count, $previousCensus?->count)),

            Stat::make('Weekly Voice', number_format($weeklyVoice))
                ->description("{$voicePercent}% of division")
                ->descriptionIcon('heroicon-m-speaker-wave')
                ->color($voicePercent >= 30 ? 'success' : ($voicePercent >= 15 ? 'warning' : 'danger')),

            Stat::make('Tags in Use', $tagCount)
                ->description("{$taggedMemberCount} members tagged")
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
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
