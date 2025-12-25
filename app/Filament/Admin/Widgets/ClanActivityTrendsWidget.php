<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Census;
use App\Models\Division;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ClanActivityTrendsWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '30' => '30 Days',
            '90' => '90 Days',
            '365' => '1 Year',
        ];
    }

    public function getHeading(): string
    {
        $days = $this->filter ?? '30';
        $label = match ($days) {
            '365' => '1 Year',
            default => "{$days} Days",
        };

        return "Clan Activity Trends (Last {$label})";
    }

    protected function getData(): array
    {
        $activeDivisionIds = Division::whereHas('members')->pluck('id');
        $days = (int) ($this->filter ?? 30);

        $trends = Census::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(count) as total_population'),
            DB::raw('SUM(weekly_voice_count) as total_voice')
        )
            ->whereIn('division_id', $activeDivisionIds)
            ->groupBy('date')
            ->orderByDesc('date')
            ->take($days)
            ->get()
            ->reverse()
            ->values();

        $labels = $trends->map(fn ($t) => Carbon::parse($t->date)->format('M j'))->toArray();
        $population = $trends->pluck('total_population')->toArray();
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
