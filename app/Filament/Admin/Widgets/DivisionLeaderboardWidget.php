<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Division;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DivisionLeaderboardWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Division Leaderboards';

    public ?string $leaderboardType = 'voice';

    public function table(Table $table): Table
    {
        return match ($this->leaderboardType) {
            'recruiting' => $this->recruitingLeaderboard($table),
            default      => $this->voiceLeaderboard($table),
        };
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
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label('Division')
                    ->url(fn (Division $record) => route('division', $record->slug)),

                TextColumn::make('latestCensus.count')
                    ->label('Members')
                    ->alignCenter()
                    ->default(0),

                TextColumn::make('latestCensus.weekly_voice_count')
                    ->label('Weekly Voice')
                    ->alignCenter()
                    ->default(0),

                TextColumn::make('voice_rate')
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
                Action::make('viewRecruiting')
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
                TextColumn::make('rank')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label('Division')
                    ->url(fn (Division $record) => route('division', $record->slug)),

                TextColumn::make('recruits_count')
                    ->label('Recruits (30 days)')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('members_count')
                    ->label('Total Members')
                    ->alignCenter()
                    ->state(fn (Division $record) => $record->members()->count()),
            ])
            ->defaultSort('recruits_count', 'desc')
            ->headerActions([
                Action::make('viewVoice')
                    ->label('Voice Leaders')
                    ->icon('heroicon-o-speaker-wave')
                    ->action(fn () => $this->leaderboardType = 'voice'),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
