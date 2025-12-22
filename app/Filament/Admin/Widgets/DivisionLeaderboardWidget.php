<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Division;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DivisionLeaderboardWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Division Leaderboards';

    public ?string $leaderboardType = 'activity';

    public function table(Table $table): Table
    {
        return match ($this->leaderboardType) {
            'voice' => $this->voiceLeaderboard($table),
            'recruiting' => $this->recruitingLeaderboard($table),
            default => $this->activityLeaderboard($table),
        };
    }

    protected function activityLeaderboard(Table $table): Table
    {
        return $table
            ->query(
                Division::query()
                    ->whereHas('members')
                    ->with('latestCensus')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Division')
                    ->url(fn (Division $record) => route('division', $record->abbreviation)),

                Tables\Columns\TextColumn::make('latestCensus.count')
                    ->label('Members')
                    ->alignCenter()
                    ->default(0),

                Tables\Columns\TextColumn::make('latestCensus.weekly_active_count')
                    ->label('Weekly Active')
                    ->alignCenter()
                    ->default(0),

                Tables\Columns\TextColumn::make('activity_rate')
                    ->label('Activity Rate')
                    ->alignCenter()
                    ->state(function (Division $record) {
                        $census = $record->latestCensus;
                        if (! $census || $census->count == 0) {
                            return 0;
                        }

                        return round(($census->weekly_active_count / $census->count) * 100);
                    })
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn ($state) => $state >= 50 ? 'success' : ($state >= 25 ? 'warning' : 'danger'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            "(SELECT weekly_active_count * 100.0 / NULLIF(count, 0) FROM censuses
                              WHERE censuses.division_id = divisions.id
                              ORDER BY id DESC LIMIT 1) {$direction}"
                        );
                    }),
            ])
            ->defaultSort('activity_rate', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('viewVoice')
                    ->label('Voice Leaders')
                    ->icon('heroicon-o-speaker-wave')
                    ->action(fn () => $this->leaderboardType = 'voice'),
                Tables\Actions\Action::make('viewRecruiting')
                    ->label('Top Recruiters')
                    ->icon('heroicon-o-user-plus')
                    ->action(fn () => $this->leaderboardType = 'recruiting'),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }

    protected function voiceLeaderboard(Table $table): Table
    {
        return $table
            ->query(
                Division::query()
                    ->whereHas('members')
                    ->with('latestCensus')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Division')
                    ->url(fn (Division $record) => route('division', $record->abbreviation)),

                Tables\Columns\TextColumn::make('latestCensus.count')
                    ->label('Members')
                    ->alignCenter()
                    ->default(0),

                Tables\Columns\TextColumn::make('latestCensus.weekly_voice_count')
                    ->label('Weekly Voice')
                    ->alignCenter()
                    ->default(0),

                Tables\Columns\TextColumn::make('voice_rate')
                    ->label('Voice Rate')
                    ->alignCenter()
                    ->state(function (Division $record) {
                        $census = $record->latestCensus;
                        if (! $census || $census->count == 0) {
                            return 0;
                        }

                        return round(($census->weekly_voice_count / $census->count) * 100);
                    })
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn ($state) => $state >= 30 ? 'success' : ($state >= 15 ? 'warning' : 'danger'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            "(SELECT weekly_voice_count * 100.0 / NULLIF(count, 0) FROM censuses
                              WHERE censuses.division_id = divisions.id
                              ORDER BY id DESC LIMIT 1) {$direction}"
                        );
                    }),
            ])
            ->defaultSort('voice_rate', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('viewActivity')
                    ->label('Activity Leaders')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->action(fn () => $this->leaderboardType = 'activity'),
                Tables\Actions\Action::make('viewRecruiting')
                    ->label('Top Recruiters')
                    ->icon('heroicon-o-user-plus')
                    ->action(fn () => $this->leaderboardType = 'recruiting'),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }

    protected function recruitingLeaderboard(Table $table): Table
    {
        return $table
            ->query(
                Division::query()
                    ->whereHas('members')
                    ->withCount(['members as recruits_count' => function (Builder $query) {
                        $query->where('join_date', '>=', now()->subDays(30));
                    }])
                    ->orderByDesc('recruits_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Division')
                    ->url(fn (Division $record) => route('division', $record->abbreviation)),

                Tables\Columns\TextColumn::make('recruits_count')
                    ->label('Recruits (30 days)')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('Total Members')
                    ->alignCenter()
                    ->state(fn (Division $record) => $record->members()->count()),
            ])
            ->defaultSort('recruits_count', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('viewActivity')
                    ->label('Activity Leaders')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->action(fn () => $this->leaderboardType = 'activity'),
                Tables\Actions\Action::make('viewVoice')
                    ->label('Voice Leaders')
                    ->icon('heroicon-o-speaker-wave')
                    ->action(fn () => $this->leaderboardType = 'voice'),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
