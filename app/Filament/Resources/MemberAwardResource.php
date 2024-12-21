<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberAwardResource\Pages;
use App\Models\MemberAward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MemberAwardResource extends Resource
{
    protected static ?string $model = MemberAward::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Division';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('award_id')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('member_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('reason')
                    ->columnSpanFull()
                    ->maxLength(191)
                    ->default(null),

                Forms\Components\Section::make('Metadata')->schema([
                    Forms\Components\DateTimePicker::make('created_at'),
                    Forms\Components\DateTimePicker::make('updated_at'),
                    Forms\Components\DateTimePicker::make('expires_at'),
                ])->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('award.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.name')->searchable(),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListMemberAwards::route('/'),
            'create' => Pages\CreateMemberAward::route('/create'),
            'edit' => Pages\EditMemberAward::route('/{record}/edit'),
        ];
    }
}
