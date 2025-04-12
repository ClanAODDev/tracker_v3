<?php

namespace App\Filament\Mod\Resources\MemberResource\RelationManagers;

use App\Enums\Rank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RankActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'rankActions';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('rank')
                    ->options(Rank::class)
                    ->required(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Effective'),
            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rank')
            ->modifyQueryUsing(fn ($query) => $query->approvedAndAccepted())
            ->columns([
                Tables\Columns\TextColumn::make('rank'),
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
