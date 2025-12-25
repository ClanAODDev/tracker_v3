<?php

namespace App\Filament\Mod\Widgets;

use App\Enums\TagVisibility;
use App\Models\Division;
use App\Models\DivisionTag;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TagUsageWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Tag Usage';

    public function table(Table $table): Table
    {
        $division = $this->getDivision();

        return $table
            ->query(
                DivisionTag::query()
                    ->when($division, function (Builder $query) use ($division) {
                        $query->forDivision($division->id)
                            ->withCount(['members' => function (Builder $q) use ($division) {
                                $q->where('division_id', $division->id);
                            }]);
                    })
                    ->orderByDesc('members_count')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Tag')
                    ->badge()
                    ->color(fn (DivisionTag $record) => match ($record->visibility->value) {
                        'public' => 'success',
                        'officers' => 'warning',
                        'senior_leaders' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('visibility')
                    ->label('Visibility')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'public' => 'success',
                        'officers' => 'warning',
                        'senior_leaders' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('members_count')
                    ->label('Members')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('division.name')
                    ->label('Scope')
                    ->default('Global')
                    ->badge()
                    ->color(fn (DivisionTag $record) => $record->division_id ? 'info' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('id')
                    ->label('Tag')
                    ->options(fn () => DivisionTag::forDivision($this->getDivision()?->id)
                        ->visibleTo()
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options([
                        TagVisibility::PUBLIC->value => TagVisibility::PUBLIC->label(),
                        TagVisibility::OFFICERS->value => TagVisibility::OFFICERS->label(),
                        TagVisibility::SENIOR_LEADERS->value => TagVisibility::SENIOR_LEADERS->label(),
                    ]),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No tags found')
            ->emptyStateDescription('Create tags to organize and track your division members.');
    }

    protected function getDivision(): ?Division
    {
        return Auth::user()?->division;
    }
}
