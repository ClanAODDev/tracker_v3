<?php

namespace App\Filament\Resources;

use App\Enums\DiscordStatus;
use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Division';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Section::make('Clan Data')->schema([
                    TextInput::make('clan_id')
                        ->required()
                        ->numeric(),
                    Forms\Components\Select::make('rank')
                        ->options(Rank::class)
                        ->required(),
                    Select::make('position')
                        ->required()
                        ->options(Position::class),
                    TextInput::make('recruiter_id')
                        ->numeric()
                        ->default(null),
                    Select::make('division_id')
                        ->relationship('division', 'name')
                        ->label('Division')
                        ->required(),
                ])->columns(2),
                Forms\Components\Section::make('Communications')->schema([
                    TextInput::make('ts_unique_id')
                        ->maxLength(255)
                        ->default(null),
                    TextInput::make('discord')
                        ->maxLength(191)
                        ->default(null),
                    Select::make('last_voice_status')
                        ->options(DiscordStatus::class)
                        ->default(null),
                    TextInput::make('discord_id')
                        ->numeric()
                        ->default(null),
                ])->columns(2),
                Forms\Components\Section::make('Activity')->schema([
                    Forms\Components\DateTimePicker::make('last_voice_activity'),
                    Forms\Components\DateTimePicker::make('last_activity'),
                    Forms\Components\DateTimePicker::make('last_ts_activity'),
                ])->columns(3),

                Forms\Components\Section::make('Dates')->schema([
                    Forms\Components\DateTimePicker::make('join_date'),
                    Forms\Components\DateTimePicker::make('last_promoted_at'),
                    Forms\Components\DateTimePicker::make('last_trained_at'),
                    TextInput::make('last_trained_by')
                        ->numeric()
                        ->default(null),
                    Forms\Components\DateTimePicker::make('xo_at'),
                    Forms\Components\DateTimePicker::make('co_at'),
                ]),

                Forms\Components\Section::make('Forum Metadata')->schema([
                    Forms\Components\Section::make('Flags')->schema([
                        Forms\Components\Toggle::make('flagged_for_inactivity')->required(),
                        Forms\Components\Toggle::make('privacy_flag')
                            ->required(),
                        Forms\Components\Toggle::make('allow_pm')
                            ->required(),
                    ])->columns(3),

                    Forms\Components\Section::make('Misc')->schema([
                        TextInput::make('posts')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('groups')
                            ->readOnly(),
                    ]),
                ]),
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
                Tables\Columns\TextColumn::make('rank')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('platoon.name'),
                Tables\Columns\TextColumn::make('squad.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ts_unique_id')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('discord')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_voice_status')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('discord_id')
                    ->toggleable()
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
                Tables\Columns\TextColumn::make('last_voice_activity')
                    ->toggleable()
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
                    ->label('Assigned XO')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('co_at')
                    ->label('Assigned CO')
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
                SelectFilter::make('division')
                    ->relationship('division', 'name'),
                Filter::make('rank_id')
                    ->label('Rank')
                    ->indicator('Rank')
                    ->form([
                        Select::make('rank')
                            ->options(Rank::class),
                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['rank'],
                                fn (Builder $query, $rank): Builder => $query->where('rank', $rank),
                            );
                    })->indicateUsing(function (array $data) {
                        return $data['rank'] ? 'Rank: ' . Rank::from($data['rank'])->getLabel() : null;
                    }),
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
