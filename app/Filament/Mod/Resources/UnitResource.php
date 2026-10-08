<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\UnitResource\Pages\EditUnit;
use App\Filament\Mod\Resources\UnitResource\Pages\ListUnits;
use App\Filament\Mod\Resources\UnitResource\RelationManagers\ChildrenRelationManager;
use App\Filament\Mod\Resources\UnitResource\RelationManagers\MembersRelationManager;
use App\Models\Division;
use App\Models\Member;
use App\Models\Unit;
use App\Rules\HoldsNoOtherPosition;
use App\Rules\ResolvesToImage;
use App\Services\Units\UnitAssignment;
use Closure;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $modelLabel = 'unit';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-square-2-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Organization';

    public static function shouldRegisterNavigation(): bool
    {
        $division = auth()->user()?->member?->division;

        return $division !== null && ! $division->isFlat();
    }

    public static function form(Schema $schema): Schema
    {
        $divisionId = Division::whereSlug(request('division'))->first()->id ?? auth()->user()->member->division_id;

        return $schema
            ->columns(1)
            ->components(fn (?Unit $record) => [

                Hidden::make('division_id')->default($divisionId),

                Section::make('Basic Info')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('description')
                            ->placeholder('Optional tagline or description')
                            ->maxLength(255)
                            ->default(null)
                            ->visible(fn (?Unit $record) => $record === null || $record->isPlatoon()),
                        TextInput::make('logo')
                            ->placeholder('https://')
                            ->maxLength(255)
                            ->default(null)
                            ->rule(new ResolvesToImage),
                    ])->columns(),

                Section::make('Leadership')
                    ->columnSpanFull()
                    ->hiddenOn('create')
                    ->schema([
                        Select::make('leader_id')
                            ->label('Leader')
                            ->searchable()
                            ->reactive()
                            ->getSearchResultsUsing(function (string $search) use ($record) {
                                $divisionId = $record->division_id;

                                if (! $divisionId) {
                                    return [];
                                }

                                return Member::query()
                                    ->where('division_id', $divisionId)
                                    ->where(function ($query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('clan_id', 'like', "%{$search}%");
                                    })
                                    ->orderBy('name')
                                    ->limit(50)
                                    ->pluck('name', 'clan_id');
                            })
                            ->getOptionLabelUsing(fn ($value) => Member::where('clan_id', $value)->value('name'))
                            ->helperText('Leave blank if position not yet assigned. Must be from the same division as the unit being assigned.')
                            ->rule(fn (?Unit $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                if (! $value) {
                                    return;
                                }

                                if (! Member::where('clan_id', $value)->where('division_id', $record->division_id)->exists()) {
                                    $fail('The selected leader must be a member of this division.');
                                }
                            })
                            ->rule(fn (?Unit $record) => new HoldsNoOtherPosition(exceptUnit: $record))
                            ->nullable(),

                        Hidden::make('original_leader_id')
                            ->reactive()
                            ->afterStateHydrated(fn (callable $set, $state, $record) => $set('original_leader_id', $record?->leader_id)),

                        Placeholder::make('Note: Changing Leadership')
                            ->content(fn (?Unit $record) => sprintf(
                                "This change will update the new leader's position to %s and the previous leader's position to Member. Will also reassign the new leader to this %s.",
                                $record?->isSquad() ? 'Squad Leader' : 'Platoon Leader',
                                strtolower($record?->levelLabel() ?? 'unit'),
                            ))
                            ->visible(fn (callable $get) => $get('leader_id') && $get('leader_id') !== $get('original_leader_id')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextInputColumn::make('order')
                    ->width('10px')
                    ->sortable()
                    ->updateStateUsing(function (Unit $record, $state) {
                        app(UnitAssignment::class)->update($record, ['order' => (int) $state]);

                        return $state;
                    }),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('level')
                    ->state(fn (Unit $record) => $record->levelLabel())
                    ->badge()
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('depth', $direction)),
                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->default('--'),
                TextColumn::make('leader.name')
                    ->default('--')
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('division_id', auth()->user()->member->division_id))
            ->defaultSort('path')
            ->filters([
                SelectFilter::make('depth')
                    ->label('Level')
                    ->options(fn () => Division::with('unitLevels')
                        ->find(auth()->user()->member->division_id)
                        ?->unitLevels
                        ->pluck('label', 'depth')
                        ->all() ?? []),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                RestoreAction::make()->using(function (Unit $record) {
                    app(UnitAssignment::class)->restore($record);

                    return true;
                }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ChildrenRelationManager::class,
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnits::route('/'),
            'edit'  => EditUnit::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['division.unitLevels', 'parent', 'leader'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
