<?php

namespace App\Filament\Mod\Resources;

use App\Enums\ActivityType;
use App\Filament\Mod\Resources\ActivityResource\Pages\ListActivities;
use App\Models\Activity;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static ?string $modelLabel = 'Activity';

    protected static ?string $pluralModelLabel = 'Activity Log';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    protected static ?int $navigationSort = 100;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->isRole(['sr_ldr', 'admin']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('division.name')
                    ->label('Division')
                    ->sortable()
                    ->searchable()
                    ->visible(fn () => auth()->user()->isRole('admin')),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->dateTimeTooltip('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Action')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ActivityType ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ActivityType ? $state->badgeColor() : 'gray'),
                TextColumn::make('user.name')
                    ->label('Performed By')
                    ->default('System')
                    ->searchable(),
                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->default('—')
                    ->url(fn ($record) => $record->subject instanceof \App\Models\Member ? route('member', $record->subject->getUrlParams()) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('properties')
                    ->label('Details')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return '—';
                        }

                        return collect($state)
                            ->filter()
                            ->map(fn ($value, $key) => ucfirst($key) . ': ' . $value)
                            ->join(', ');
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('division_id')
                    ->label('Division')
                    ->options(fn () => \App\Models\Division::active()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn () => auth()->user()->isRole('admin')),
                SelectFilter::make('name')
                    ->label('Activity Type')
                    ->options(ActivityType::options()),
                SelectFilter::make('user_id')
                    ->label('Performed By')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->isRole('admin')) {
                            return \App\Models\User::whereHas('member')
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        }
                        $divisionId = $user->member?->division_id;

                        return \App\Models\User::whereHas('member', fn ($q) => $q->where('division_id', $divisionId))
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                Filter::make('date_range')
                    ->form([
                        Select::make('period')
                            ->label('Time Period')
                            ->options([
                                'today' => 'Today',
                                '7'     => 'Last 7 days',
                                '30'    => 'Last 30 days',
                                '90'    => 'Last 90 days',
                            ])
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $until = Carbon::today();
                                $from  = match ($state) {
                                    'today' => Carbon::today(),
                                    '7'     => Carbon::now()->subDays(7),
                                    '30'    => Carbon::now()->subDays(30),
                                    '90'    => Carbon::now()->subDays(90),
                                    default => null,
                                };
                                $set('from', $from?->format('Y-m-d'));
                                $set('until', $state ? $until->format('Y-m-d') : null);
                            }),
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->columns(3)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From ' . Carbon::parse($data['from'])->format('M j, Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until ' . Carbon::parse($data['until'])->format('M j, Y');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(2)
            ->paginated([25, 50, 100])
            ->poll('60s');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user  = auth()->user();
        $query = parent::getEloquentQuery()
            ->whereIn('name', ActivityType::values())
            ->with(['user', 'subject', 'division']);

        if (! $user->isRole('admin')) {
            $query->where('division_id', $user->member?->division_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
