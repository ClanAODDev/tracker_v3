<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Census;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ClanActivityTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Clan Activity Trends (Last 30 Days)';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $trends = Census::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(count) as total_population'),
            DB::raw('SUM(weekly_active_count) as total_active'),
            DB::raw('SUM(weekly_voice_count) as total_voice')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->take(30)
            ->get();

        $labels = $trends->map(fn ($t) => \Carbon\Carbon::parse($t->date)->format('M j'))->toArray();
        $population = $trends->pluck('total_population')->toArray();
        $weeklyActive = $trends->pluck('total_active')->toArray();
        $weeklyVoice = $trends->pluck('total_voice')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Population',
                    'data' => $population,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Weekly Active',
                    'data' => $weeklyActive,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Weekly Voice',
                    'data' => $weeklyVoice,
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
