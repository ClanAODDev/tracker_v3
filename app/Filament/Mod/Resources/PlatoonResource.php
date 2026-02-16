<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\PlatoonResource\Pages\EditPlatoon;
use App\Filament\Mod\Resources\PlatoonResource\Pages\ListPlatoons;
use App\Filament\Mod\Resources\PlatoonResource\RelationManagers\MembersRelationManager;
use App\Filament\Mod\Resources\PlatoonResource\RelationManagers\SquadsRelationManager;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
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
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlatoonResource extends Resource
{
    protected static ?string $model = Platoon::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-square-2-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Organization';

    public static function form(Schema $schema): Schema
    {
        $divisionId = Division::whereSlug(request('division'))->first()->id ?? auth()->user()->member->division_id;

        return $schema
            ->columns(1)
            ->components(fn (?Platoon $record) => [

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
                            ->default(null),
                        TextInput::make('logo')
                            ->placeholder('https://')
                            ->maxLength(255)
                            ->default(null),
                    ])->columns(),

                Section::make('Leadership')
                    ->columnSpanFull()
                    ->hiddenOn('create')
                    ->schema([
                        Select::make('leader_id')
                            ->label('Leader')
                            ->default('--')
                            ->searchable()
                            ->reactive()
                            ->getSearchResultsUsing(function (string $search) use ($record) {
                                $divisionId = $record->division_id;

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
                            ->helperText('Leave blank if position not yet assigned. Must be from the same division as the platoon being assigned.')
                            ->nullable(),

                        Hidden::make('original_leader_id')
                            ->reactive()
                            ->afterStateHydrated(fn (callable $set, $state, $record) => $set('original_leader_id',
                                $record?->leader_id)
                            ),
                        Placeholder::make('Note: Changing Leadership')
                            ->content("This change will update the new leader's position to Platoon Leader and the previous leader's position to Member. Will also reassign the new leader to this platoon")
                            ->visible(fn (callable $get
                            ) => $get('leader_id') && $get('leader_id') !== $get('original_leader_id')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextInputColumn::make('order')
                    ->width('10px')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('leader.name')
                    ->numeric()
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
            ])->modifyQueryUsing(function ($query) {
                $query->where('division_id', auth()->user()->member->division_id);
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
            SquadsRelationManager::class,
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlatoons::route('/'),
            'edit'  => EditPlatoon::route('/{record}/edit'),
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
