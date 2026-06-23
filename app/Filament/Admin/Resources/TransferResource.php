<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransferResource\Pages\CreateTransfer;
use App\Filament\Admin\Resources\TransferResource\Pages\EditTransfer;
use App\Filament\Admin\Resources\TransferResource\Pages\ListTransfers;
use App\Models\Transfer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Divisions';

    protected static ?int $navigationSort = 4;

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
                DateTimePicker::make('created_at')
                    ->label('Requested At')
                    ->default(fn () => now())
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('approved_at', $state);
                    })
                    ->live()
                    ->required(),
                DateTimePicker::make('approved_at')
                    ->label('Approved At')
                    ->default(fn () => now())
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('approved_by', $state ? auth()->id() : null);
                    })
                    ->live()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('division.name')
                    ->sortable(),
                TextColumn::make('member.name')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Pending'),
                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
