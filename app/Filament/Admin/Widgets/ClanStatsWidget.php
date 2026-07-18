<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Census;
use App\Models\ClanSnapshot;
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
        $latest = ClanSnapshot::recent()->first();

        if ($latest) {
            return $this->statsFromSnapshot($latest);
        }

        return $this->statsFromLiveAggregation();
    }

    protected function statsFromSnapshot(ClanSnapshot $snapshot): array
    {
        $recruitsLastMonth = Member::whereBetween('join_date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->count();

        $history = ClanSnapshot::recent()->limit(14)->get()->reverse()->values();

        return [
            Stat::make('Total Clan Members', number_format($snapshot->total_members))
                ->description("{$snapshot->active_divisions} active divisions")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->chart($history->pluck('total_members')->all())
                ->color('primary'),

            Stat::make('Clan Weekly Voice', number_format($snapshot->weekly_voice_count))
                ->description("{$snapshot->voice_participation}% voice participation")
                ->descriptionIcon('heroicon-m-speaker-wave')
                ->chart($history->pluck('weekly_voice_count')->all())
                ->color($snapshot->voice_participation >= 30 ? 'success' : ($snapshot->voice_participation >= 15 ? 'warning' : 'danger')),

            Stat::make('Recruits This Month', number_format($snapshot->monthly_recruits))
                ->description($this->getRecruitTrendDescription($snapshot->monthly_recruits, $recruitsLastMonth))
                ->descriptionIcon($this->getRecruitTrendIcon($snapshot->monthly_recruits, $recruitsLastMonth))
                ->chart($history->pluck('monthly_recruits')->all())
                ->color($this->getRecruitTrendColor($snapshot->monthly_recruits, $recruitsLastMonth)),
        ];
    }

    protected function statsFromLiveAggregation(): array
    {
        $activeDivisionIds = Division::whereHas('members')->pluck('id');
        $activeDivisions   = $activeDivisionIds->count();

        $latestCensuses = Census::select('division_id', DB::raw('MAX(id) as id'))
            ->whereIn('division_id', $activeDivisionIds)
            ->groupBy('division_id')
            ->pluck('id');

        $clanStats = Census::whereIn('id', $latestCensuses)
            ->selectRaw('SUM(count) as total_count, SUM(weekly_voice_count) as total_voice')
            ->first();

        $totalFromCensus = $clanStats->total_count ?? Member::whereIn('division_id', $activeDivisionIds)->count();
        $totalVoice      = $clanStats->total_voice ?? 0;

        $voicePercent = $totalFromCensus > 0 ? round(($totalVoice / $totalFromCensus) * 100) : 0;

        $recruitsThisMonth = Member::where('join_date', '>=', now()->startOfMonth())->count();
        $recruitsLastMonth = Member::whereBetween('join_date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        ])->count();

        return [
            Stat::make('Total Clan Members', number_format($totalFromCensus))
                ->description("{$activeDivisions} active divisions")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->chart($this->getLivePopulationTrend($activeDivisionIds))
                ->color('primary'),

            Stat::make('Clan Weekly Voice', number_format($totalVoice))
                ->description("{$voicePercent}% voice participation")
                ->descriptionIcon('heroicon-m-speaker-wave')
                ->chart($this->getLiveVoiceTrend($activeDivisionIds))
                ->color($voicePercent >= 30 ? 'success' : ($voicePercent >= 15 ? 'warning' : 'danger')),

            Stat::make('Recruits This Month', number_format($recruitsThisMonth))
                ->description($this->getRecruitTrendDescription($recruitsThisMonth, $recruitsLastMonth))
                ->descriptionIcon($this->getRecruitTrendIcon($recruitsThisMonth, $recruitsLastMonth))
                ->chart($this->getLiveRecruitsTrend())
                ->color($this->getRecruitTrendColor($recruitsThisMonth, $recruitsLastMonth)),
        ];
    }

    protected function getLivePopulationTrend($divisionIds): array
    {
        return Census::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(count) as total'))
            ->whereIn('division_id', $divisionIds)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take(14)
            ->pluck('total')
            ->reverse()
            ->values()
            ->toArray();
    }

    protected function getLiveVoiceTrend($divisionIds): array
    {
        return Census::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(weekly_voice_count) as total'))
            ->whereIn('division_id', $divisionIds)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take(14)
            ->pluck('total')
            ->reverse()
            ->values()
            ->toArray();
    }

    protected function getLiveRecruitsTrend(): array
    {
        $results = Member::where('join_date', '>=', now()->subDays(98))
            ->selectRaw('FLOOR(DATEDIFF(CURDATE(), DATE(join_date)) / 7) as week_ago, COUNT(*) as count')
            ->groupBy('week_ago')
            ->pluck('count', 'week_ago');

        $data = [];
        for ($i = 13; $i >= 0; $i--) {
            $data[] = (int) ($results[$i] ?? 0);
        }

        return $data;
    }

    protected function getRecruitTrendDescription(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? 'New recruiting activity' : 'No recruiting yet';
        }

        $diff    = $current - $previous;
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
