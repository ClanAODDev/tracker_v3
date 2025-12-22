<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Census;
use App\Models\Division;
use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ClanStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalMembers = Member::count();
        $activeDivisions = Division::whereHas('members')->count();

        $latestCensuses = Census::select('division_id', DB::raw('MAX(id) as id'))
            ->groupBy('division_id')
            ->pluck('id');

        $clanStats = Census::whereIn('id', $latestCensuses)
            ->selectRaw('SUM(count) as total_count, SUM(weekly_voice_count) as total_voice')
            ->first();

        $totalFromCensus = $clanStats->total_count ?? $totalMembers;
        $totalVoice = $clanStats->total_voice ?? 0;

        $voicePercent = $totalFromCensus > 0 ? round(($totalVoice / $totalFromCensus) * 100) : 0;

        $recruitsThisMonth = Member::where('join_date', '>=', now()->startOfMonth())->count();
        $recruitsLastMonth = Member::whereBetween('join_date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->count();

        $populationTrend = $this->getClanPopulationTrend();

        return [
            Stat::make('Total Clan Members', number_format($totalFromCensus))
                ->description("{$activeDivisions} active divisions")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->chart($populationTrend)
                ->color('primary'),

            Stat::make('Clan Weekly Voice', number_format($totalVoice))
                ->description("{$voicePercent}% voice participation")
                ->descriptionIcon('heroicon-m-speaker-wave')
                ->color($voicePercent >= 30 ? 'success' : ($voicePercent >= 15 ? 'warning' : 'danger')),

            Stat::make('Recruits This Month', number_format($recruitsThisMonth))
                ->description($this->getRecruitTrendDescription($recruitsThisMonth, $recruitsLastMonth))
                ->descriptionIcon($this->getRecruitTrendIcon($recruitsThisMonth, $recruitsLastMonth))
                ->color($this->getRecruitTrendColor($recruitsThisMonth, $recruitsLastMonth)),
        ];
    }

    protected function getClanPopulationTrend(): array
    {
        return Census::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(count) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->take(14)
            ->pluck('total')
            ->toArray();
    }

    protected function getRecruitTrendDescription(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? 'New recruiting activity' : 'No recruiting yet';
        }

        $diff = $current - $previous;
        $percent = round(abs($diff / $previous) * 100);

        if ($diff > 0) {
            return "+{$percent}% vs last month";
        } elseif ($diff < 0) {
            return "-{$percent}% vs last month";
        }

        return 'Same as last month';
    }

    protected function getRecruitTrendIcon(int $current, int $previous): string
    {
        if ($current > $previous) {
            return 'heroicon-m-arrow-trending-up';
        } elseif ($current < $previous) {
            return 'heroicon-m-arrow-trending-down';
        }

        return 'heroicon-m-minus';
    }

    protected function getRecruitTrendColor(int $current, int $previous): string
    {
        if ($current > $previous) {
            return 'success';
        } elseif ($current < $previous) {
            return 'danger';
        }

        return 'gray';
    }
}
