<?php

namespace App\Filament\Admin\Resources;

use App\Enums\DiscordStatus;
use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Admin\Resources\MemberHasManyAwardsResource\RelationManagers\AwardsRelationManager;
use App\Filament\Admin\Resources\MemberResource\Pages\EditMember;
use App\Filament\Admin\Resources\MemberResource\Pages\ListMembers;
use App\Models\Member;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Section::make('Clan Data')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('clan_id')
                            ->required()
                            ->numeric(),
                        Select::make('rank')
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
                    ])->columns(),

                Section::make('Communications')
                    ->columnSpanFull()
                    ->schema([
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
                            ->readOnly()
                            ->default(null),
                    ])->columns(),

                Section::make('Activity')
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('last_voice_activity'),
                        DateTimePicker::make('last_activity'),
                        DateTimePicker::make('last_ts_activity'),
                    ])->columns(3),

                Section::make('Dates')
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('join_date'),
                        DateTimePicker::make('last_promoted_at'),
                        DateTimePicker::make('last_trained_at'),
                        TextInput::make('last_trained_by')
                            ->numeric()
                            ->default(null),
                        DateTimePicker::make('xo_at'),
                        DateTimePicker::make('co_at'),
                    ])->columns(3),

                Section::make('Forum Metadata')
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Flags')->schema([
                            Toggle::make('flagged_for_inactivity')->required(),
                            Toggle::make('privacy_flag')
                                ->required(),
                            Toggle::make('allow_pm')
                                ->required(),
                        ])->columns(3),

                        Section::make('Misc')->schema([
                            TextInput::make('posts')
                                ->required()
                                ->numeric()
                                ->default(0),
                            Textarea::make('groups')
                                ->readOnly(),
                        ])->columns(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('clan_id')
                    ->sortable(),
                TextColumn::make('rank')
                    ->sortable()
                    ->badge(),
                TextColumn::make('platoon.name')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('squad.name')
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('position')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ts_unique_id')
                    ->toggleable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discord')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('last_voice_status')
                    ->toggleable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discord_id')
                    ->toggleable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('flagged_for_inactivity')
                    ->boolean(),
                TextColumn::make('posts')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('privacy_flag')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('allow_pm')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('join_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_activity')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_voice_activity')
                    ->toggleable()
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_ts_activity')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_promoted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_trained_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_trained_by')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('xo_at')
                    ->label('Assigned XO')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('co_at')
                    ->label('Assigned CO')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('recruiter.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('division')
                    ->relationship('division', 'name'),
                Filter::make('Has Active Division')
                    ->query(function (Builder $query) {
                        $query->whereNotNull('division_id')
                            ->whereHas('division', function (Builder $subQuery) {
                                $subQuery->where('active', true);
                            });
                    })
                    ->label('Has Active Division')
                    ->default(),
                Filter::make('rank_id')
                    ->label('Rank')
                    ->indicator('Rank')
                    ->schema([
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
                SelectFilter::make('position')
                    ->options(Position::class),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AwardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'edit'  => EditMember::route('/{record}/edit'),
        ];
    }
}
