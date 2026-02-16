<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransferResource\Pages\CreateTransfer;
use App\Filament\Admin\Resources\TransferResource\Pages\EditTransfer;
use App\Filament\Admin\Resources\TransferResource\Pages\ListTransfers;
use App\Models\Transfer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->label('Division')
                    ->searchable()
                    ->required(),
                Select::make('member_id')
                    ->relationship('member', 'name')
                    ->label('Member')
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('member.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Transfer Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index'  => ListTransfers::route('/'),
            'create' => CreateTransfer::route('/create'),
            'edit'   => EditTransfer::route('/{record}/edit'),
        ];
    }
}
