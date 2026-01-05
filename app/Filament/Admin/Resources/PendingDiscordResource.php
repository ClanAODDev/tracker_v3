<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PendingDiscordResource\Pages\ListPendingDiscord;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PendingDiscordResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Pending Registration';

    protected static ?string $pluralModelLabel = 'Pending Registrations';

    protected static ?string $slug = 'pending-discord';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->pendingDiscord();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('discord_username')
                    ->label('Discord Username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discord_id')
                    ->label('Discord ID')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPendingDiscord::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count() ?: null;
    }
}
