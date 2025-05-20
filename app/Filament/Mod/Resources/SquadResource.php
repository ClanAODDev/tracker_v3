<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\SquadResource\Pages;
use App\Models\Squad;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SquadResource extends Resource
{
    protected static ?string $model = Squad::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Platoons';

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']) || auth()->user()->isDivisionLeader();
    }

    public static function canEdit(Model $record): bool
    {
        if (auth()->user()->isRole(['admin', 'sr_ldr']) || auth()->user()->isDivisionLeader()) {
            return true;
        }

        // allow platoon leader of squad to edit
        if (auth()->user()->isPlatoonLeader() && $record->platoon->leader_id === auth()->user()->member_id) {
            return true;
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(fn(Squad $record) => [
                Forms\Components\Section::make('Basic Info')->schema([
                    Forms\Components\TextInput::make('name')
                        ->maxLength(255)
                        ->default(null),
                    Forms\Components\TextInput::make('logo')
                        ->maxLength(191)
                        ->default(null),
                    Select::make('platoon_id')
                        ->relationship('platoon', 'name')
                        ->label('Platoon')
                        ->options(\App\Models\Platoon::whereDivisionId(auth()->user()->member->division_id)->pluck('name',
                            'id'))
                        ->hiddenOn('edit')
                        ->required(),
                ]),
                Forms\Components\Section::make('Leadership')->schema([
                    Select::make('leader_id')
                        ->label('Leader')
                        ->searchable()
                        ->reactive()
                        ->getSearchResultsUsing(function (string $search) use ($record) {
                            $divisionId = $record->platoon->division_id;
                            if (!$divisionId) {
                                return [];
                            }

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
                        ->getOptionLabelUsing(fn($value) => \App\Models\Member::where('clan_id', $value)->value('name'))
                        ->helperText('Leave blank if position not yet assigned. Must be from the same division as the squad being assigned.')
                        ->nullable(),

                    Hidden::make('original_leader_id')
                        ->reactive()
                        ->afterStateHydrated(fn(callable $set, $state, $record) =>
                        $set('original_leader_id', $record?->leader_id)
                        ),
                    Forms\Components\Placeholder::make('Note: Changing Leadership')
                        ->content("This change will update the new leader's position to Squad Leader and the previous leader's position to Member. Will also reassign the new leader to this platoon and squad")
                        ->visible( fn(callable $get) => $get('leader_id') && $get('leader_id') !== $get('original_leader_id'))
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('platoon.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name'),
                Tables\Columns\TextColumn::make('leader.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->modifyQueryUsing(function ($query) {
                $query->whereHas('platoon', function ($query) {
                    $query->where('division_id', auth()->user()->member->division_id);
                });
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSquads::route('/'),
            'create' => Pages\CreateSquad::route('/create'),
            'edit' => Pages\EditSquad::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
