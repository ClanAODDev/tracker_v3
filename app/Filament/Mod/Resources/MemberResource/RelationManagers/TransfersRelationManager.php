<?php

namespace App\Filament\Mod\Resources\MemberResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransfersRelationManager extends RelationManager
{
    protected static string $relationship = 'transfers';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('division_id')
                    ->relationship('division', 'name')
                    ->label('Division')
                    ->required(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Effective')
                    ->readOnly()->default(now()),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('division')
            ->columns([
                Tables\Columns\TextColumn::make('division.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Effective')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
