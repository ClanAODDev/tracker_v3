<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CensusResource\Pages;
use App\Models\Census;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CensusResource extends Resource
{
    protected static ?string $model = Census::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->label('Division')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('count')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('weekly_active_count')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('weekly_ts_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('weekly_voice_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weekly_active_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weekly_ts_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weekly_voice_count')
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
            'index' => Pages\ListCensuses::route('/'),
            'create' => Pages\CreateCensus::route('/create'),
            'edit' => Pages\EditCensus::route('/{record}/edit'),
        ];
    }
}
