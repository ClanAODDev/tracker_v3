<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Position;
use App\Enums\Rank;
use App\Filament\Admin\Resources\MemberHasManyAwardsResource\RelationManagers\AwardsRelationManager;
use App\Filament\Forms\Components\IngameHandlesForm;
use App\Filament\Forms\Components\PartTimeDivisionsForm;
use App\Filament\Mod\Resources\MemberResource\Pages;
use App\Filament\Mod\Resources\MemberResource\RelationManagers\NotesRelationManager;
use App\Filament\Mod\Resources\MemberResource\RelationManagers\RankActionsRelationManager;
use App\Filament\Mod\Resources\MemberResource\RelationManagers\TransfersRelationManager;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Division';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Clan Data')->schema([
                    TextInput::make('clan_id')
                        ->readOnly()
                        ->required()
                        ->numeric(),
                    Select::make('recruiter_id')
                        ->relationship('recruiter', 'name')
                        ->searchable()
                        ->nullable(),
                    Select::make('last_trained_by')
                        ->label('Last Trained By')
                        ->helperText('Update when NCO training occurs')
                        ->searchable()
                        ->relationship('trainer', 'name'),
                    TextInput::make('position')
                        ->label('Position')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Manage by editing division, platoon, or squad')
                        ->formatStateUsing(fn ($state) => Position::from($state)->getLabel()),
                ])->columns(),

                Forms\Components\Section::make('Communications')->schema([
                    TextInput::make('ts_unique_id')
                        ->disabled(),
                    TextInput::make('discord')
                        ->disabled(),
                    TextInput::make('discord_id')
                        ->disabled(),
                ])->columns(3),
                Forms\Components\Section::make('Activity')->schema([
                    Forms\Components\DateTimePicker::make('last_voice_activity')->readOnly(),
                    Forms\Components\DateTimePicker::make('last_activity')->readOnly(),
                ])->columns(),

                Forms\Components\Section::make('Dates')->schema([
                    Forms\Components\DateTimePicker::make('join_date')->disabled(),
                    Forms\Components\DateTimePicker::make('last_promoted_at')->disabled(),
                    Forms\Components\DateTimePicker::make('last_trained_at')->disabled(),
                ])->columns(3),

                Forms\Components\Section::make('Division Assignment')
                    ->schema([
                        Forms\Components\Placeholder::make('Division')
                            ->content(fn (Member $record): string => $record->division?->name ?? 'None'),
                        Select::make('platoon_id')
                            ->nullable(true)
                            ->label('Platoon')
                            ->relationship('platoon', 'name')
                            ->options(function (Get $get) {
                                $divisionId = $get('division_id');

                                return Platoon::where('division_id', $divisionId)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->afterStateHydrated(function ($state, callable $set) {
                                if ($state === 0) {
                                    $set('platoon_id', null);
                                }
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('squad_id', null);
                            }),
                        Select::make('squad_id')
                            ->label('Squad')
                            ->nullable(true)
                            ->relationship('squad', 'name')
                            ->afterStateHydrated(function ($state, callable $set) {
                                if ($state === 0) {
                                    $set('squad_id', null);
                                }
                            })
                            ->options(function (Get $get) {
                                $platoonId = $get('platoon_id');

                                if ($platoonId) {
                                    return Squad::where('platoon_id', $platoonId)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }

                                return [];
                            }),
                    ])->columns(3),

                Forms\Components\Section::make('Part-time Divisions')
                    ->id('part-time-divisions')
                    ->collapsible()
                    ->collapsed()
                    ->description('Select any additional divisions this member is part-time in.')
                    ->schema([
                        PartTimeDivisionsForm::makeUsingFormModel(),
                    ]),

                Forms\Components\Section::make('In-game Handles')
                    ->id('ingame-handles')
                    ->description('In-game handles and alts for this member.')
                    ->collapsed()
                    ->collapsible()
                    ->schema([
                        IngameHandlesForm::make()
                            ->default(fn ($record) => $record
                                ? IngameHandlesForm::getGroupedHandles($record)
                                : []
                            ),
                    ]),

                Forms\Components\Section::make('Forum Metadata')
                    ->description('Forum settings and metadata for this member.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Section::make('Flags')->schema([
                            Forms\Components\Toggle::make('flagged_for_inactivity')
                                ->disabled(),
                            Forms\Components\Toggle::make('privacy_flag')
                                ->disabled(),
                            Forms\Components\Toggle::make('allow_pm')
                                ->disabled(),
                        ])->columns(3),

                        Forms\Components\Section::make('Misc')->schema([
                            TextInput::make('posts')
                                ->disabled()
                                ->numeric()
                                ->default(0),
                            Forms\Components\Textarea::make('groups')
                                ->disabled(),
                        ])->columns(),
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
                Tables\Columns\TextColumn::make('platoon.name')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('squad.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('position')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('division.name')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([

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

                Filter::make('unit')
                    ->label('Unit')
                    ->indicator('Unit')
                    ->form([

                        Select::make('division')
                            ->label('Division')
                            ->options(Division::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->default(optional(auth()->user()->member)->division_id)
                            ->live()
                            ->afterStateUpdated(function (callable $set) {

                                $set('platoon', []);
                                $set('squad', []);
                            }),

                        Select::make('platoon')
                            ->label('Platoon')
                            ->options(function (callable $get) {
                                $divisionId = $get('division');
                                if (! $divisionId) {
                                    return [];
                                }

                                return Platoon::where('division_id', $divisionId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (callable $get) => empty($get('division')))
                            ->afterStateUpdated(function (callable $set) {
                                $set('squad', []);
                            }),

                        Select::make('squad')
                            ->label('Squad')
                            ->options(function (callable $get) {
                                $platoons = (array) ($get('platoon') ?? []);
                                if (empty($platoons)) {
                                    return [];
                                }

                                return Squad::whereIn('platoon_id', $platoons)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (callable $get) => empty($get('platoon'))),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $division = $data['division'] ?? null;
                        $platoons = $data['platoon'] ?? [];
                        $squads = $data['squad'] ?? [];

                        if ($squads) {
                            return $query->whereIn('squad_id', $squads);
                        }
                        if ($platoons) {
                            return $query->whereIn('platoon_id', $platoons);
                        }
                        if ($division) {
                            return $query->where('division_id', $division);
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data) {
                        $parts = [];

                        if (! empty($data['division'])) {
                            if ($name = Division::whereKey($data['division'])->value('name')) {
                                $parts[] = "Division: {$name}";
                            }
                        }
                        if (! empty($data['platoon'])) {
                            $parts[] = 'Platoon: ' . Platoon::whereIn('id', $data['platoon'])->pluck('name')->implode(', ');
                        }
                        if (! empty($data['squad'])) {
                            $parts[] = 'Squad: ' . Squad::whereIn('id', $data['squad'])->pluck('name')->implode(', ');
                        }

                        return $parts ? implode(' | ', $parts) : null;
                    }),

                Filter::make('rank_id')
                    ->label('Rank')
                    ->indicator('Rank')
                    ->form([
                        Select::make('rank')
                            ->options(Rank::class)
                            ->multiple()
                            ->placeholder('Select ranks...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['rank']) && is_array($data['rank']) && count($data['rank']) > 0) {
                            return $query->whereIn('rank', $data['rank']);
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data) {
                        if (isset($data['rank']) && is_array($data['rank']) && count($data['rank']) > 0) {
                            return 'Rank: ' . implode(', ', array_map(function ($rank) {
                                return Rank::from($rank)->getLabel();
                            }, $data['rank']));
                        }

                        return null;
                    }),
                Filter::make('Has Active Division')
                    ->query(function (Builder $query) {
                        $query->whereNotNull('division_id')
                            ->whereHas('division', function (Builder $subQuery) {
                                $subQuery->where('active', true);
                            });
                    })
                    ->label('Has Active Division')
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('member_transfer')
                        ->label('Transfer member(s)')
                        ->modalWidth('lg')
                        ->modalDescription('Only members of the same division can be transferred.')
                        ->visible(fn (): bool => auth()->user()->isRole(['admin', 'sr_ldr']))
                        ->icon('heroicon-o-adjustments-vertical')
                        ->form([
                            Select::make('platoon_id')
                                ->label('Platoon')
                                ->options(fn (HasTable $livewire): array => Platoon::with('division')
                                    ->where('division_id', $livewire
                                        ->getSelectedTableRecords()
                                        ->pluck('division_id')
                                        ->first()
                                    )
                                    ->get()
                                    ->mapWithKeys(fn (Platoon $p) => [
                                        $p->id => "{$p->division->name} – {$p->name}",
                                    ])
                                    ->toArray()
                                )
                                ->required()
                                ->searchable()
                                ->reactive(),

                            Select::make('squad_id')
                                ->label('Squad')
                                ->options(fn (callable $get) => Squad::where('platoon_id', $get('platoon_id'))
                                    ->pluck('name', 'id')
                                    ->toArray()
                                )
                                ->searchable()
                                ->disabled(fn (callable $get) => ! $get('platoon_id')),
                        ])
                        ->before(function (Collection $records, BulkAction $action): bool {
                            if ($records->pluck('division_id')->unique()->count() > 1) {
                                Notification::make()
                                    ->danger()
                                    ->title('Multiple divisions selected')
                                    ->body('Only members of the same division can be transferred')
                                    ->persistent()
                                    ->send();

                                $action->cancel();
                            }

                            return true;
                        })
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update([
                                'platoon_id' => $data['platoon_id'],
                                'squad_id' => $data['squad_id'],
                            ]);
                        })
                        ->color('primary'),
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
            'tags' => Pages\ManageMemberTags::route('/tags'),
        ];
    }
}
