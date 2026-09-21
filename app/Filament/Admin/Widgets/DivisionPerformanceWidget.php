<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Census;
use App\Models\Division;
use App\Models\Member;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
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
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Division')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Division $record) => route('division', $record->slug)),

                TextColumn::make('latestCensus.count')
                    ->label('Members')
                    ->sortable()
                    ->alignCenter()
                    ->default(fn (Division $record) => $record->members_count),

                TextColumn::make('latestCensus.weekly_voice_count')
                    ->label('Weekly Voice')
                    ->alignCenter()
                    ->default(0),

                TextColumn::make('voice_rate')
                    ->label('Voice %')
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Census::selectRaw('CASE WHEN count = 0 THEN 0 ELSE weekly_voice_count / count END')
                                ->whereColumn('division_id', 'divisions.id')
                                ->latest('created_at')
                                ->limit(1),
                            $direction,
                        );
                    })
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

                TextColumn::make('recruits_this_month')
                    ->label('Recruits (30d)')
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount(['members as recruits_this_month_count' => function ($q) {
                            $q->where('join_date', '>=', now()->subDays(30));
                        }])->orderBy('recruits_this_month_count', $direction);
                    })
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
