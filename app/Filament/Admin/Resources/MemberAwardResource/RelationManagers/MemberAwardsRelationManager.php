<?php

namespace App\Filament\Admin\Resources\MemberAwardResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberAwardsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('award_id')
                    ->relationship('award', 'name'),

                Forms\Components\Select::make('member_id')
                    ->searchable()
                    ->relationship('member', 'name'),

                Forms\Components\Textarea::make('reason')
                    ->columnSpanFull()
                    ->maxLength(191)
                    ->default(null),

                Forms\Components\Toggle::make('approved'),

                Forms\Components\Section::make('Metadata')->schema([
                    Forms\Components\DateTimePicker::make('created_at')->default(now()),
                    Forms\Components\DateTimePicker::make('updated_at')->default(now()),
                ])->columns(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('member.name')
            ->columns([
                Tables\Columns\TextColumn::make('member.name'),
                Tables\Columns\ToggleColumn::make('approved')->label('Approved'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Given At'),
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
