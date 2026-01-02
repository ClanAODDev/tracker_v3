<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CensusResource\Pages\CreateCensus;
use App\Filament\Admin\Resources\CensusResource\Pages\EditCensus;
use App\Filament\Admin\Resources\CensusResource\Pages\ListCensuses;
use App\Models\Census;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CensusResource extends Resource
{
    protected static ?string $model = Census::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Data';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->label('Division')
                    ->searchable()
                    ->required(),
                TextInput::make('count')
                    ->required()
                    ->numeric(),
                TextInput::make('weekly_active_count')
                    ->required()
                    ->numeric(),
                TextInput::make('weekly_ts_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('weekly_voice_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weekly_active_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weekly_ts_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weekly_voice_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('division')
                    ->relationship('division', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListCensuses::route('/'),
            'create' => CreateCensus::route('/create'),
            'edit' => EditCensus::route('/{record}/edit'),
        ];
    }
}
