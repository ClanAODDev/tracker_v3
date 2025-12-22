<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Census;
use App\Models\Division;
use App\Models\Member;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class DivisionPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Division Performance Overview';

    public function table(Table $table): Table
    {
        $latestCensusIds = Census::select('division_id', DB::raw('MAX(id) as id'))
            ->groupBy('division_id')
            ->pluck('id');

        return $table
            ->query(
                Division::query()
                    ->whereHas('members')
                    ->withCount('members')
                    ->with(['latestCensus'])
                    ->orderByDesc('members_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Division')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Division $record) => route('division', $record->abbreviation)),

                Tables\Columns\TextColumn::make('latestCensus.count')
                    ->label('Members')
                    ->sortable()
                    ->alignCenter()
                    ->default(fn (Division $record) => $record->members_count),

                Tables\Columns\TextColumn::make('latestCensus.weekly_voice_count')
                    ->label('Weekly Voice')
                    ->alignCenter()
                    ->default(0),

                Tables\Columns\TextColumn::make('voice_rate')
                    ->label('Voice %')
                    ->alignCenter()
                    ->state(function (Division $record) {
                        $census = $record->latestCensus;
                        if (! $census || $census->count == 0) {
                            return '0%';
                        }

                        return round(($census->weekly_voice_count / $census->count) * 100) . '%';
                    })
                    ->badge()
                    ->color(function (Division $record) {
                        $census = $record->latestCensus;
                        if (! $census || $census->count == 0) {
                            return 'gray';
                        }
                        $rate = ($census->weekly_voice_count / $census->count) * 100;

                        return $rate >= 30 ? 'success' : ($rate >= 15 ? 'warning' : 'danger');
                    }),

                Tables\Columns\TextColumn::make('recruits_this_month')
                    ->label('Recruits (30d)')
                    ->alignCenter()
                    ->state(fn (Division $record) => Member::where('division_id', $record->id)
                        ->where('join_date', '>=', now()->subDays(30))
                        ->count())
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('members_count', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
