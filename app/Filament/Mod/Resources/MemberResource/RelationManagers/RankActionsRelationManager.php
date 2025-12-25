<?php

namespace App\Filament\Mod\Resources\MemberResource\RelationManagers;

use App\Enums\Rank;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RankActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'rankActions';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('rank')
                    ->options(Rank::class)
                    ->required(),
                DateTimePicker::make('created_at')
                    ->label('Effective'),
                RichEditor::make('justification')
                    ->hidden(fn ($record) => $record->justification === null),
            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rank')
            ->modifyQueryUsing(fn ($query) => $query->approvedAndAccepted())
            ->columns([
                TextColumn::make('rank'),
                TextColumn::make('created_at')
                    ->label('Effective')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
