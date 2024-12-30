<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\SquadResource\Pages;
use App\Models\Squad;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SquadResource extends Resource
{
    protected static ?string $model = Squad::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Division Organization';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('logo')
                    ->maxLength(191)
                    ->default(null),
                Select::make('platoon_id')
                    ->relationship('platoon', 'name')
                    ->label('Platoon')
                    ->options(\App\Models\Platoon::whereDivisionId(auth()->user()->member->division_id)->pluck('name', 'id'))
                    ->required(),
                Select::make('leader_id')
                    ->relationship('leader', 'name')
                    ->label('Leader')
                    ->searchable()
                    ->helperText('Leave blank if position not yet assigned')
                    ->nullable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
}
