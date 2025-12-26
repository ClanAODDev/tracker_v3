<?php

namespace App\Filament\Admin\Resources\MemberHasManyAwardsResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AwardsRelationManager extends RelationManager
{
    protected static string $relationship = 'awards';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('award_id')
                    ->relationship('award', 'name'),

                Textarea::make('reason')
                    ->columnSpanFull()
                    ->maxLength(191)
                    ->default(null),

                Toggle::make('approved')->hiddenOn('create'),

                Section::make('Metadata')
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('created_at')->default(now()),
                        DateTimePicker::make('updated_at')->default(now()),
                    ])->columns(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('award.image'),
                TextColumn::make('award.name'),
                TextColumn::make('created_at'),
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
