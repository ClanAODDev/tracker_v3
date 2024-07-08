<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('clan_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('rank_id')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('platoon_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('squad_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('position_id')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('division_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ts_unique_id')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('discord')
                    ->maxLength(191)
                    ->default(null),
                Forms\Components\DateTimePicker::make('last_voice_activity'),
                Forms\Components\TextInput::make('last_voice_status')
                    ->maxLength(191)
                    ->default(null),
                Forms\Components\TextInput::make('discord_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\Toggle::make('flagged_for_inactivity')
                    ->required(),
                Forms\Components\TextInput::make('posts')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('privacy_flag')
                    ->required(),
                Forms\Components\Toggle::make('allow_pm')
                    ->required(),
                Forms\Components\DateTimePicker::make('join_date'),
                Forms\Components\DateTimePicker::make('last_activity'),
                Forms\Components\DateTimePicker::make('last_ts_activity'),
                Forms\Components\DateTimePicker::make('last_promoted_at'),
                Forms\Components\DateTimePicker::make('last_trained_at'),
                Forms\Components\TextInput::make('last_trained_by')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('xo_at'),
                Forms\Components\DateTimePicker::make('co_at'),
                Forms\Components\TextInput::make('recruiter_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('groups')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('clan_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rank_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('platoon_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('squad_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ts_unique_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discord')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_voice_activity')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_voice_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discord_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('flagged_for_inactivity')
                    ->boolean(),
                Tables\Columns\TextColumn::make('posts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('privacy_flag')
                    ->boolean(),
                Tables\Columns\IconColumn::make('allow_pm')
                    ->boolean(),
                Tables\Columns\TextColumn::make('join_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_activity')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_ts_activity')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_promoted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_trained_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_trained_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('xo_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('co_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recruiter_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
