<?php

namespace App\Filament\Mod\Widgets;

use App\Models\Division;
use App\Models\Member;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecruitmentWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Recruitment Activity';

    public ?string $tableView = 'recent';

    public function table(Table $table): Table
    {
        $division = $this->getDivision();

        if ($this->tableView === 'recruiters') {
            return $this->topRecruitersTable($table, $division);
        }

        return $this->recentRecruitsTable($table, $division);
    }

    protected function recentRecruitsTable(Table $table, ?Division $division): Table
    {
        return $table
            ->query(
                Member::query()
                    ->when($division, fn (Builder $q) => $q->where('division_id', $division->id))
                    ->whereNotNull('join_date')
                    ->where('join_date', '>=', now()->subDays(30))
                    ->orderByDesc('join_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Member')
                    ->searchable()
                    ->url(fn (Member $record) => route('member', $record->clan_id)),

                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getAbbreviation()),

                Tables\Columns\TextColumn::make('recruiter.name')
                    ->label('Recruited By')
                    ->default('Unknown')
                    ->url(fn (Member $record) => $record->recruiter ? route('member', $record->recruiter->clan_id) : null),

                Tables\Columns\TextColumn::make('join_date')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('viewRecruiters')
                    ->label('Top Recruiters')
                    ->icon('heroicon-o-trophy')
                    ->action(fn () => $this->tableView = 'recruiters'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No recent recruits')
            ->emptyStateDescription('No members have joined in the last 30 days.');
    }

    protected function topRecruitersTable(Table $table, ?Division $division): Table
    {
        return $table
            ->query(
                Member::query()
                    ->when($division, fn (Builder $q) => $q->where('division_id', $division->id))
                    ->whereHas('recruits', function (Builder $q) use ($division) {
                        $q->where('join_date', '>=', now()->subDays(90));
                        if ($division) {
                            $q->where('division_id', $division->id);
                        }
                    })
                    ->withCount(['recruits' => function (Builder $q) use ($division) {
                        $q->where('join_date', '>=', now()->subDays(90));
                        if ($division) {
                            $q->where('division_id', $division->id);
                        }
                    }])
                    ->orderByDesc('recruits_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Recruiter')
                    ->searchable()
                    ->url(fn (Member $record) => route('member', $record->clan_id)),

                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getAbbreviation()),

                Tables\Columns\TextColumn::make('recruits_count')
                    ->label('Recruits (90 days)')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('viewRecent')
                    ->label('Recent Recruits')
                    ->icon('heroicon-o-user-plus')
                    ->action(fn () => $this->tableView = 'recent'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No recruiters found')
            ->emptyStateDescription('No members have recruited anyone in the last 90 days.');
    }

    protected function getDivision(): ?Division
    {
        return Auth::user()?->division;
    }
}
