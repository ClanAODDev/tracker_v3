<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Leave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Leave of Absence';

    protected static ?string $pluralLabel = 'Leaves of Absence';

    protected static ?string $navigationGroup = 'Division';

    protected static ?string $navigationParentItem = 'Divisions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('member_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('approver_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('requester_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('reason')
                    ->required(),
                Forms\Components\TextInput::make('note_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('end_date')
                    ->required(),
                Forms\Components\Toggle::make('extended'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason'),
                Tables\Columns\TextColumn::make('note_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('extended')
                    ->boolean(),
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
