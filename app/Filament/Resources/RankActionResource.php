<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RankActionResource\Pages;
use App\Models\RankAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RankActionResource extends Resource
{
    protected static ?string $model = RankAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Divisions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('member_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('rank_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('division_id')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rank_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division_id')
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
            ])
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
            'index' => Pages\ListRankActions::route('/'),
            'create' => Pages\CreateRankAction::route('/create'),
            'edit' => Pages\EditRankAction::route('/{record}/edit'),
        ];
    }
}
