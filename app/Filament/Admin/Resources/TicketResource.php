<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TicketResource\Pages\CreateTicket;
use App\Filament\Admin\Resources\TicketResource\Pages\EditTicket;
use App\Filament\Admin\Resources\TicketResource\Pages\ListTickets;
use App\Filament\Admin\Resources\TicketResource\RelationManagers\CommentsRelationManager;
use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    public static function getNavigationBadge(): ?string
    {
        $count = Ticket::whereIn('state', ['new', 'assigned'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket Information')->schema([
                    Select::make('ticket_type_id')
                        ->relationship('type', 'name')
                        ->label('Ticket Type')
                        ->required()
                        ->disabled(fn ($operation) => $operation === 'edit'),

                    Select::make('state')
                        ->options([
                            'new' => 'New',
                            'assigned' => 'Assigned',
                            'resolved' => 'Resolved',
                            'rejected' => 'Rejected',
                        ])
                        ->required()
                        ->default('new')
                        ->native(false),

                    Select::make('division_id')
                        ->label('Division')
                        ->searchable()
                        ->relationship('division', 'name')
                        ->disabled(fn ($operation) => $operation === 'edit'),

                    Textarea::make('description')
                        ->required()
                        ->minLength(25)
                        ->rows(6)
                        ->columnSpanFull(),
                ])->columns(3),

                Section::make('Assignment')->schema([
                    Select::make('caller_id')
                        ->label('Caller')
                        ->searchable()
                        ->relationship('caller', 'name')
                        ->disabled(fn ($operation) => $operation === 'edit'),

                    Select::make('owner_id')
                        ->label('Assigned To')
                        ->searchable()
                        ->options(fn () => User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->pluck('name', 'id'))
                        ->placeholder('Unassigned'),

                    DateTimePicker::make('resolved_at')
                        ->label('Resolved At')
                        ->visible(fn ($record) => $record?->state === 'resolved' || $record?->state === 'rejected'),
                ])->columns(3),

                Section::make('Discord Integration')->schema([
                    TextInput::make('external_message_id')
                        ->label('Discord Message ID')
                        ->readOnly()
                        ->maxLength(36),
                ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('state')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'assigned' => 'warning',
                        'resolved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('type.name')
                    ->label('Type')
                    ->sortable(),

                TextColumn::make('caller.name')
                    ->label('Caller')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('owner.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->sortable(),

                TextColumn::make('division.name')
                    ->label('Division')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('state')
                    ->options([
                        'new' => 'New',
                        'assigned' => 'Assigned',
                        'resolved' => 'Resolved',
                        'rejected' => 'Rejected',
                    ])
                    ->multiple(),

                SelectFilter::make('ticket_type_id')
                    ->relationship('type', 'name')
                    ->label('Type'),

                TernaryFilter::make('assigned')
                    ->label('Assignment')
                    ->placeholder('All tickets')
                    ->trueLabel('Assigned only')
                    ->falseLabel('Unassigned only')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('owner_id'),
                        false: fn (Builder $query) => $query->whereNull('owner_id'),
                    ),

                SelectFilter::make('division_id')
                    ->relationship('division', 'name')
                    ->label('Division'),
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
            CommentsRelationManager::class,
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
