<?php

namespace App\Filament\Mod\Resources\DivisionTagResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rank')
                    ->badge()
                    ->sortable(),
                TextColumn::make('platoon.name')
                    ->label('Platoon')
                    ->default('—')
                    ->sortable(),
                TextColumn::make('squad.name')
                    ->label('Squad')
                    ->default('—')
                    ->sortable(),
                TextColumn::make('pivot.created_at')
                    ->label('Tagged At')
                    ->dateTime()
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('member_tag.created_at', $direction)),
            ])
            ->defaultSort('member_tag.created_at', 'desc')
            ->recordActions([
                DetachAction::make()->label('Remove'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('Remove'),
                ]),
            ]);
    }
}
