<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MemberRequestResource\Pages\CreateMemberRequest;
use App\Filament\Admin\Resources\MemberRequestResource\Pages\EditMemberRequest;
use App\Filament\Admin\Resources\MemberRequestResource\Pages\ListMemberRequests;
use App\Models\MemberRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemberRequestResource extends Resource
{
    protected static ?string $model = MemberRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('requester_id')
                    ->required()
                    ->numeric(),
                TextInput::make('member_id')
                    ->required()
                    ->numeric(),
                TextInput::make('division_id')
                    ->required()
                    ->numeric(),
                TextInput::make('approver_id')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('cancelled_at'),
                TextInput::make('canceller_id')
                    ->numeric()
                    ->default(null),
                Textarea::make('notes')
                    ->maxLength(255)
                    ->default(null),
                DateTimePicker::make('hold_placed_at'),
                DateTimePicker::make('processed_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name'),
                TextColumn::make('division.name'),
                TextColumn::make('requester.name'),
                TextColumn::make('approver.name'),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cancelled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('canceller_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('notes')
                    ->searchable(),
                TextColumn::make('hold_placed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('processed_at')
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
            'index' => ListMemberRequests::route('/'),
            'create' => CreateMemberRequest::route('/create'),
            'edit' => EditMemberRequest::route('/{record}/edit'),
        ];
    }
}
