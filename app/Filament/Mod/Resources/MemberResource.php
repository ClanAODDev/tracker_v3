<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Admin\Resources\MemberHasManyAwardsResource\RelationManagers\AwardsRelationManager;
use App\Filament\Mod\Resources\MemberResource\Pages;
use App\Filament\Mod\Resources\MemberResource\RelationManagers\NotesRelationManager;
use App\Filament\Mod\Resources\MemberResource\RelationManagers\RankActionsRelationManager;
use App\Filament\Mod\Resources\MemberResource\RelationManagers\TransfersRelationManager;
use App\Models\Division;
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
use Illuminate\Database\Eloquent\Model;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Division';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        if ($record->id === auth()->user()->member_id) {
            return false;
        }

        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->readOnly()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Section::make('Clan Data')->schema([
                    TextInput::make('clan_id')
                        ->readOnly()
                        ->required()
                        ->numeric(),
                    Select::make('position')
                        ->required()
                        ->options(Position::class),
                    Select::make('recruiter_id')
                        ->relationship('recruiter', 'name')
                        ->searchable()
                        ->nullable(),
                    Select::make('last_trained_by')
                        ->label('Last Trained By')
                        ->searchable()
                        ->relationship('trainer', 'name'),
                ])->columns(2),
                Forms\Components\Section::make('Communications')->schema([
                    TextInput::make('ts_unique_id')
                        ->disabled(),
                    TextInput::make('discord')
                        ->disabled(),
                    TextInput::make('discord_id')
                        ->disabled(),
                ])->columns(),
                Forms\Components\Section::make('Activity')->schema([
                    Forms\Components\DateTimePicker::make('last_voice_activity')->readOnly(),
                    Forms\Components\DateTimePicker::make('last_activity')->readOnly(),
                ])->columns(),

                Forms\Components\Section::make('Dates')->schema([
                    Forms\Components\DateTimePicker::make('join_date')->readOnly(),
                    Forms\Components\DateTimePicker::make('last_promoted_at')->readOnly(),
                    Forms\Components\DateTimePicker::make('last_trained_at')->readOnly(),
                ])->columns(3),

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
                            ->readOnly()
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
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('division_id')
                    ->options(Division::active()->get()->pluck('name', 'id')),
                Filter::make('Has Active Division')
                    ->query(function (Builder $query) {
                        $query->whereNotNull('division_id')
                            ->whereHas('division', function (Builder $subQuery) {
                                $subQuery->where('active', true);
                            });
                    })
                    ->label('Has Active Division')
                    ->default(),

                Filter::make('position')
                    ->form([
                        Select::make('position')
                            ->options(Position::class),
                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['position'],
                                fn (Builder $query, $position): Builder => $query->where('position', $position),
                            );
                    }),
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
                    //                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AwardsRelationManager::class,
            NotesRelationManager::class,
            RankActionsRelationManager::class,
            TransfersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
