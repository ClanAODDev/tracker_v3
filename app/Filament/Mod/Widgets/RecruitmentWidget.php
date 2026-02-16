<?php

namespace App\Filament\Mod\Widgets;

use App\Models\Division;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecruitmentWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public ?string $tableView = 'recent';

    public int $days = 30;

    public function table(Table $table): Table
    {
        $division = $this->getDivision();

        if ($this->tableView === 'recruiters') {
            return $this->topRecruitersTable($table, $division);
        }

        return $this->recentRecruitsTable($table, $division);
    }

    protected function getTableHeading(): string
    {
        $label = match ($this->days) {
            365     => '1 Year',
            default => "{$this->days} Days",
        };

        return $this->tableView === 'recruiters'
            ? "Top Recruiters (Last {$label})"
            : "Recent Recruits (Last {$label})";
    }

    protected function recentRecruitsTable(Table $table, ?Division $division): Table
    {
        return $table
            ->query(
                Member::query()
                    ->with('recruiter')
                    ->when($division, fn (Builder $q) => $q->where('division_id', $division->id))
                    ->whereNotNull('join_date')
                    ->where('join_date', '>=', now()->subDays($this->days))
                    ->orderByDesc('join_date')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Member')
                    ->searchable()
                    ->url(fn (Member $record) => $record->clan_id ? route('member', $record->clan_id) : null),

                TextColumn::make('rank')
                    ->label('Rank')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getAbbreviation()),

                TextColumn::make('recruiter.name')
                    ->label('Recruited By')
                    ->default('Unknown')
                    ->url(fn (Member $record) => $record->recruiter ? route('member', $record->recruiter->clan_id) : null),

                TextColumn::make('join_date')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('viewRecruiters')
                    ->label('Top Recruiters')
                    ->icon('heroicon-o-trophy')
                    ->action(fn () => $this->tableView = 'recruiters'),
                ActionGroup::make([
                    Action::make('30days')
                        ->label('30 Days')
                        ->action(fn () => $this->days = 30),
                    Action::make('90days')
                        ->label('90 Days')
                        ->action(fn () => $this->days = 90),
                    Action::make('365days')
                        ->label('1 Year')
                        ->action(fn () => $this->days = 365),
                ])->label('Time Range')->icon('heroicon-o-calendar')->button(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No recent recruits')
            ->emptyStateDescription('No members have joined in the selected time period.');
    }

    protected function topRecruitersTable(Table $table, ?Division $division): Table
    {
        return $table
            ->query(
                Member::query()
                    ->when($division, fn (Builder $q) => $q->where('division_id', $division->id))
                    ->whereHas('recruits', function (Builder $q) use ($division) {
                        $q->where('join_date', '>=', now()->subDays($this->days));
                        if ($division) {
                            $q->where('division_id', $division->id);
                        }
                    })
                    ->withCount(['recruits' => function (Builder $q) use ($division) {
                        $q->where('join_date', '>=', now()->subDays($this->days));
                        if ($division) {
                            $q->where('division_id', $division->id);
                        }
                    }])
                    ->orderByDesc('recruits_count')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Recruiter')
                    ->searchable()
                    ->url(fn (Member $record) => route('member', $record->clan_id)),

                TextColumn::make('rank')
                    ->label('Rank')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getAbbreviation()),

                TextColumn::make('recruits_count')
                    ->label('Recruits')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
            ])
            ->headerActions([
                Action::make('viewRecent')
                    ->label('Recent Recruits')
                    ->icon('heroicon-o-user-plus')
                    ->action(fn () => $this->tableView = 'recent'),
                ActionGroup::make([
                    Action::make('30days')
                        ->label('30 Days')
                        ->action(fn () => $this->days = 30),
                    Action::make('90days')
                        ->label('90 Days')
                        ->action(fn () => $this->days = 90),
                    Action::make('365days')
                        ->label('1 Year')
                        ->action(fn () => $this->days = 365),
                ])->label('Time Range')->icon('heroicon-o-calendar')->button(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No recruiters found')
            ->emptyStateDescription('No members have recruited anyone in the selected time period.');
    }

    protected function getDivision(): ?Division
    {
        return Auth::user()?->division;
    }
}
