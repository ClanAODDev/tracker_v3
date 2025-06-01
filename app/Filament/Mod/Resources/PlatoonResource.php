<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\PlatoonResource\Pages;
use App\Filament\Mod\Resources\PlatoonResource\RelationManagers\MembersRelationManager;
use App\Filament\Mod\Resources\PlatoonResource\RelationManagers\SquadsRelationManager;
use App\Models\Division;
use App\Models\Platoon;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlatoonResource extends Resource
{
    protected static ?string $model = Platoon::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-2-stack';

    protected static ?string $navigationGroup = 'Organization';

    public static function form(Form $form): Form
    {
        $divisionId = Division::whereSlug(request('division'))->first()->id ?? auth()->user()->member->division_id;

        return $form
            ->schema(fn (?Platoon $record) => [

                Hidden::make('division_id')->default($divisionId),

                Forms\Components\Section::make('Basic Info')->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('logo')
                        ->placeholder('https://')
                        ->maxLength(255)
                        ->default(null),
                ])->columns(),

                Forms\Components\Section::make('Leadership')
                    ->hiddenOn('create')
                    ->schema([
                        Select::make('leader_id')
                            ->label('Leader')
                            ->default('--')
                            ->searchable()
                            ->reactive()
                            ->getSearchResultsUsing(function (string $search) use ($record) {
                                $divisionId = $record->division_id;

                                return \App\Models\Member::query()
                                    ->where('division_id', $divisionId)
                                    ->where(function ($query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('clan_id', 'like', "%{$search}%");
                                    })
                                    ->orderBy('name')
                                    ->limit(50)
                                    ->pluck('name', 'clan_id');
                            })
                            ->getOptionLabelUsing(fn ($value) => \App\Models\Member::where('clan_id',
                                $value)->value('name'))
                            ->helperText('Leave blank if position not yet assigned. Must be from the same division as the platoon being assigned.')
                            ->nullable(),

                        Hidden::make('original_leader_id')
                            ->reactive()
                            ->afterStateHydrated(fn (callable $set, $state, $record) => $set('original_leader_id',
                                $record?->leader_id)
                            ),
                        Forms\Components\Placeholder::make('Note: Changing Leadership')
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
                Tables\Columns\TextInputColumn::make('order')
                    ->width('10px')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('leader.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->modifyQueryUsing(function ($query) {
                $query->where('division_id', auth()->user()->member->division_id);
            })
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListPlatoons::route('/'),
            'edit' => Pages\EditPlatoon::route('/{record}/edit'),
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
