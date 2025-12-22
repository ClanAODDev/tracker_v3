<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\TagVisibility;
use App\Models\Division;
use App\Models\DivisionTag;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ClanTagUsageWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Tag Usage Across Clan';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DivisionTag::query()
                    ->withCount('members')
                    ->orderByDesc('members_count')
            )
            ->filters([
                Tables\Filters\SelectFilter::make('division_id')
                    ->label('Division')
                    ->options(
                        Division::whereHas('members')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->prepend('Global', '')
                    )
                    ->query(function ($query, array $data) {
                        if ($data['value'] === '') {
                            return $query->whereNull('division_id');
                        }
                        if ($data['value']) {
                            return $query->where('division_id', $data['value']);
                        }

                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options([
                        TagVisibility::PUBLIC->value => TagVisibility::PUBLIC->label(),
                        TagVisibility::OFFICERS->value => TagVisibility::OFFICERS->label(),
                        TagVisibility::SENIOR_LEADERS->value => TagVisibility::SENIOR_LEADERS->label(),
                    ]),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tag')
                    ->badge()
                    ->color(fn (DivisionTag $record) => match ($record->visibility) {
                        TagVisibility::PUBLIC => 'success',
                        TagVisibility::OFFICERS => 'warning',
                        TagVisibility::SENIOR_LEADERS => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('division.name')
                    ->label('Division')
                    ->default('Global')
                    ->badge()
                    ->color(fn (DivisionTag $record) => $record->division_id ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibility')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        TagVisibility::PUBLIC => 'success',
                        TagVisibility::OFFICERS => 'warning',
                        TagVisibility::SENIOR_LEADERS => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('Members')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
            ])
            ->defaultSort('members_count', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No tags found')
            ->emptyStateDescription('No tags have been created yet.');
    }
}
