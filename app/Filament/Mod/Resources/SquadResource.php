<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\SquadResource\Pages\EditSquad;
use App\Filament\Mod\Resources\SquadResource\Pages\ListSquads;
use App\Filament\Mod\Resources\SquadResource\RelationManagers\MembersRelationManager;
use App\Models\Member;
use App\Models\Squad;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SquadResource extends Resource
{
    protected static ?string $model = Squad::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Organization';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(fn (?Squad $record) => [
                Section::make('Basic Info')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('logo')
                            ->maxLength(191)
                            ->default(null),
                    ])->columns(),

                Section::make('Leadership')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('leader_id')
                            ->label('Leader')
                            ->searchable()
                            ->reactive()
                            ->getSearchResultsUsing(function (string $search) use ($record) {
                                $divisionId = $record->platoon->division_id;
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
                            ->getOptionLabelUsing(fn ($value) => Member::where('clan_id',
                                $value)->value('name'))
                            ->helperText('Leave blank if position not yet assigned. Must be from the same division as the squad being assigned.')
                            ->nullable(),

                        Hidden::make('original_leader_id')
                            ->reactive()
                            ->afterStateHydrated(fn (callable $set, $state, $record) => $set('original_leader_id',
                                $record?->leader_id)),

                        Placeholder::make('Note: Changing Leadership')
                            ->content("This change will update the new leader's position to Squad Leader and the previous leader's position to Member. Will also reassign the new leader to this platoon and squad")
                            ->visible(fn (callable $get
                            ) => $get('leader_id') && $get('leader_id') !== $get('original_leader_id')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('platoon.name')
                    ->sortable(),
                TextColumn::make('division.name'),
                TextColumn::make('leader.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->modifyQueryUsing(function ($query) {
                $query->whereHas('platoon', function ($query) {
                    $query->where('division_id', auth()->user()->member->division_id);
                });
            })
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSquads::route('/'),
            'edit'  => EditSquad::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
