<?php

namespace App\Filament\Mod\Widgets;

use App\Models\Division;
use App\Models\DivisionTag;
use Filament\Tables;
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Tag')
                    ->badge()
                    ->color(fn (DivisionTag $record) => match ($record->visibility->value) {
                        'public' => 'success',
                        'officers' => 'warning',
                        'senior_leaders' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibility')
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->badge()
                    ->color(fn ($state) => match ($state->value) {
                        'public' => 'success',
                        'officers' => 'warning',
                        'senior_leaders' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('Members')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('division.name')
                    ->label('Scope')
                    ->default('Global')
                    ->badge()
                    ->color(fn (DivisionTag $record) => $record->division_id ? 'info' : 'gray'),
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
