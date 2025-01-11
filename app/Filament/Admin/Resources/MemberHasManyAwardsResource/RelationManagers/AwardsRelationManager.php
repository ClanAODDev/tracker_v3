<?php

namespace App\Filament\Admin\Resources\MemberHasManyAwardsResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AwardsRelationManager extends RelationManager
{
    protected static string $relationship = 'awards';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('award_id')
                    ->relationship('award', 'name'),

                Forms\Components\Textarea::make('reason')
                    ->columnSpanFull()
                    ->maxLength(191)
                    ->default(null),

                Forms\Components\Toggle::make('approved')->hiddenOn('create'),

                Forms\Components\Section::make('Metadata')->schema([
                    Forms\Components\DateTimePicker::make('created_at')->default(now()),
                    Forms\Components\DateTimePicker::make('updated_at')->default(now()),
                ])->columns(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('award.image'),
                Tables\Columns\TextColumn::make('award.name'),
                Tables\Columns\TextColumn::make('created_at'),
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
