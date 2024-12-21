<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AwardResource\Pages;
use App\Models\Award;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AwardResource extends Resource
{
    protected static ?string $model = Award::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Admin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->directory('awards')
                    ->avatar()
                    ->columnSpanFull()
                    ->alignCenter()
                    ->image(),
                Forms\Components\TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(191),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(100),
                Forms\Components\Section::make('Metadata')->schema([
                    Forms\Components\Toggle::make('active')
                        ->required(),
                    Forms\Components\Toggle::make('allow_recommendation')
                        ->required(),
                    Forms\Components\Toggle::make('allow_request')
                        ->required(),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label(''),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->toggleable()->hidden(),
                Tables\Columns\TextColumn::make('display_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('allow_recommendation')
                    ->boolean(),
                Tables\Columns\IconColumn::make('allow_request')
                    ->boolean(),
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
            'index' => Pages\ListAwards::route('/'),
            'create' => Pages\CreateAward::route('/create'),
            'edit' => Pages\EditAward::route('/{record}/edit'),
        ];
    }
}
