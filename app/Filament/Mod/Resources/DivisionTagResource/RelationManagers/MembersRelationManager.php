<?php

namespace App\Filament\Mod\Resources\DivisionTagResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rank')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('platoon.name')
                    ->label('Platoon')
                    ->default('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('squad.name')
                    ->label('Squad')
                    ->default('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('Tagged At')
                    ->dateTime()
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('member_tag.created_at', $direction)),
            ])
            ->defaultSort('member_tag.created_at', 'desc')
            ->actions([
                Tables\Actions\DetachAction::make()->label('Remove'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('Remove'),
                ]),
            ]);
    }
}
