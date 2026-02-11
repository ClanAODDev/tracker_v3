<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Admin\Resources\TicketTypeResource\Pages\CreateTicketType;
use App\Filament\Admin\Resources\TicketTypeResource\Pages\EditTicketType;
use App\Filament\Admin\Resources\TicketTypeResource\Pages\ListTicketTypes;
use App\Models\TicketType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TicketTypeResource extends Resource
{
    protected static ?string $model = TicketType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(191),
                        TextInput::make('description')
                            ->required()
                            ->maxLength(191),
                        TextInput::make('display_order')
                            ->required()
                            ->numeric()
                            ->default(100),
                    ]),
                Section::make('Access & Assignment')
                    ->columns(2)
                    ->schema([
                        Select::make('role_access')
                            ->label('Who Can Request')
                            ->multiple()
                            ->options(Role::class)
                            ->helperText('Leave empty to allow all roles'),
                        Select::make('minimum_rank')
                            ->label('Minimum Rank to Work')
                            ->options(
                                collect(Rank::cases())
                                    ->filter(fn (Rank $rank) => $rank->value >= Rank::STAFF_SERGEANT->value)
                                    ->mapWithKeys(fn (Rank $rank) => [$rank->value => $rank->getLabel()])
                            )
                            ->helperText('Leave empty for admins only'),
                        TextInput::make('notification_channel')
                            ->label('Discord Channel')
                            ->placeholder('aod-admins')
                            ->helperText('Channel name without # (leave blank for default)'),
                        Toggle::make('include_content_in_notification')
                            ->label('Include content in notification')
                            ->helperText('Include the ticket description in the Discord channel notification'),
                        Select::make('auto_assign_to_id')
                            ->label('Auto-assign To')
                            ->relationship('auto_assign_to', 'name')
                            ->searchable(),
                    ]),
                Textarea::make('boilerplate')
                    ->label('Boilerplate Text')
                    ->rows(3)
                    ->helperText('Guidance provided when creating this ticket type e.g., Please provide...'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('display_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('minimum_rank')
                    ->label('Min Rank')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->placeholder('Admins Only'),
                TextColumn::make('auto_assign_to.name')
                    ->label('Auto-assign')
                    ->placeholder('-'),
                TextColumn::make('notification_channel')
                    ->label('Channel')
                    ->placeholder('default')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('display_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
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
            'index' => ListTicketTypes::route('/'),
            'create' => CreateTicketType::route('/create'),
            'edit' => EditTicketType::route('/{record}/edit'),
        ];
    }
}
