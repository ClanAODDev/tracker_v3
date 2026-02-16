<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\TagVisibility;
use App\Models\Division;
use App\Models\DivisionTag;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                SelectFilter::make('division_id')
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

                SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options([
                        TagVisibility::PUBLIC->value         => TagVisibility::PUBLIC->label(),
                        TagVisibility::OFFICERS->value       => TagVisibility::OFFICERS->label(),
                        TagVisibility::SENIOR_LEADERS->value => TagVisibility::SENIOR_LEADERS->label(),
                    ]),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Tag')
                    ->badge()
                    ->color(fn (DivisionTag $record) => match ($record->visibility) {
                        TagVisibility::PUBLIC         => 'success',
                        TagVisibility::OFFICERS       => 'warning',
                        TagVisibility::SENIOR_LEADERS => 'danger',
                        default                       => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('division.name')
                    ->label('Division')
                    ->default('Global')
                    ->badge()
                    ->color(fn (DivisionTag $record) => $record->division_id ? 'info' : 'gray'),

                TextColumn::make('visibility')
                    ->label('Visibility')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        TagVisibility::PUBLIC         => 'success',
                        TagVisibility::OFFICERS       => 'warning',
                        TagVisibility::SENIOR_LEADERS => 'danger',
                        default                       => 'gray',
                    }),

                TextColumn::make('members_count')
                    ->label('Members')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
            ])
            ->recordActions([
                Action::make('viewMembers')
                    ->label('View Members')
                    ->icon('heroicon-o-users')
                    ->modalHeading(fn (DivisionTag $record) => "Members with \"{$record->name}\" tag")
                    ->modalContent(fn (DivisionTag $record) => view('filament.admin.widgets.tag-members-modal', [
                        'members' => $record->members()->with('division')->orderBy('name')->get(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('members_count', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No tags found')
            ->emptyStateDescription('No tags have been created yet.');
    }
}
