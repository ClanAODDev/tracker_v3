<?php

namespace App\Filament\Admin\Resources\MemberAwardResource\RelationManagers;

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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class MemberAwardsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('award_id')
                    ->relationship('award', 'name'),

                Select::make('member_id')
                    ->searchable()
                    ->relationship('member', 'name'),

                Textarea::make('reason')
                    ->columnSpanFull()
                    ->maxLength(191)
                    ->default(null),

                Toggle::make('approved')->hiddenOn('create'),

                Section::make('Metadata')->schema([
                    DateTimePicker::make('created_at')->default(now()),
                    DateTimePicker::make('updated_at')->default(now()),
                ])->columns(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('member.name')
            ->columns([
                TextColumn::make('member.name'),
                ToggleColumn::make('approved')->label('Approved'),
                TextColumn::make('created_at')->dateTime()->label('Given At'),
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
