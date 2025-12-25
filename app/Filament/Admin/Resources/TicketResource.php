<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TicketResource\Pages\CreateTicket;
use App\Filament\Admin\Resources\TicketResource\Pages\EditTicket;
use App\Filament\Admin\Resources\TicketResource\Pages\ListTickets;
use App\Models\Ticket;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('state')
                    ->required()
                    ->default('unassigned'),
                Select::make('ticket_type_id')
                    ->relationship('type', 'name')
                    ->label('Ticket Type'),
                TextInput::make('external_message_id')
                    ->readOnly()
                    ->maxLength(36),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Select::make('caller_id')
                    ->label('Caller')
                    ->searchable()
                    ->relationship('caller', 'name'),
                Select::make('owner_id')
                    ->label('Owner')
                    ->searchable()
                    ->relationship('owner', 'name'),
                Select::make('division_id')
                    ->label('Division')
                    ->searchable()
                    ->relationship('division', 'name'),
                DateTimePicker::make('resolved_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('state'),
                TextColumn::make('type.name')
                    ->sortable(),
                TextColumn::make('caller.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('division_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('resolved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('hide_resolved')
                    ->query(fn (Builder $query): Builder => $query->whereNull('resolved_at'))
                    ->label('Hide resolved')
                    ->default(),
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
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }
}
