<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Admin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('state')
                    ->required()
                    ->default('unassigned'),
                Select::make('ticket_type_id')
                    ->relationship('type', 'name')
                    ->label('Ticket Type'),
                Forms\Components\TextInput::make('external_message_id')
                    ->readOnly()
                    ->maxLength(36),
                Forms\Components\Textarea::make('description')
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
                Forms\Components\DateTimePicker::make('resolved_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state'),
                Tables\Columns\TextColumn::make('type.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('caller.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resolved_at')
                    ->dateTime()
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
                Filter::make('hide_resolved')
                    ->query(fn (Builder $query): Builder => $query->whereNull('resolved_at'))
                    ->label('Hide resolved')
                    ->default(),
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
