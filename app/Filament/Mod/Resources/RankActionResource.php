<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource\Pages;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\MemberRelationManager;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\RequesterRelationManager;
use App\Models\Member;
use App\Models\RankAction;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RankActionResource extends Resource
{
    protected static ?string $model = RankAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Division';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        $member = $user->member;

        if (auth()->user()->isRole('admin')) {
            return (string) static::$model::where('approved_at', null)->count();
        }

        return (string) static::$model::whereHas('member', function (Builder $memberQuery) use ($user, $member) {
            $memberQuery
                ->when($user->isPlatoonLeader(), fn ($q) => $q->where('platoon_id', $member->platoon_id))
                ->when($user->isSquadLeader(), fn ($q) => $q->where('squad_id', $member->squad_id))
                ->when($user->isDivisionLeader() && ! $user->isRole('admin'),
                    fn ($q) => $q->where('division_id', $member->division_id));
        })->where('approved_at', null)->count();

        return null;
    }

    public static function canEdit(Model $record): bool
    {
        $authedMember = auth()->user()->member_id;

        if (! parent::canView($record)) {
            return false;
        }

        if ($record->requester_id == $authedMember) {
            return true;
        }

        if ($record->member_id == $authedMember) {
            return false;
        }

        if ($record->rank > auth()->user()->member->rank->value) {
            return false;
        }

        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rank Action Details')
                    ->hiddenOn('create')
                    ->schema([
                        static::getStatusFormField(),
                        Select::make('rank')
                            ->options(Rank::class)
                            ->disabledOn('edit'),
                        static::getJustificationFormField(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name'),
                Tables\Columns\TextColumn::make('rank')
                    ->sortable()
                    ->badge(),
                Tables\Columns\ViewColumn::make('status')
                    ->label('status')
                    ->view('filament.forms.components.status-badge'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requester.name'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                $member = $user->member;

                $query->whereHas('member', function (Builder $memberQuery) use ($user, $member) {
                    $memberQuery
                        ->when($user->isPlatoonLeader(), fn ($q) => $q->where('platoon_id', $member->platoon_id))
                        ->when($user->isSquadLeader(), fn ($q) => $q->where('squad_id', $member->squad_id))
                        ->when($user->isDivisionLeader() && ! $user->isRole('admin'),
                            fn ($q) => $q->where('division_id', $member->division_id));
                });
            })->modifyQueryUsing(function (Builder $query) {
                $userRank = auth()->user()->member->rank->value;
                $currentMemberId = auth()->user()->member_id;

                $query->where(function ($q) use ($userRank, $currentMemberId) {
                    $q->where(function ($q1) use ($userRank, $currentMemberId) {
                        // For rank actions not requested by the current member:
                        // - Only include those where the recommended rank is less than the current member's rank
                        // - And where the rank action’s member is not the current member.
                        $q1->where('rank', '<', $userRank)
                            ->where('member_id', '<>', $currentMemberId);
                    })
                        // OR include any rank actions where the current member is the requester.
                        ->orWhere('requester_id', $currentMemberId);
                })
                    ->where(function ($q) {
                        // Include actions that are either:
                        // - Not approved at all, OR
                        // - Approved, but not yet accepted or declined.
                        $q->whereNull('approved_at')
                            ->orWhere(function ($q2) {
                                $q2->whereNotNull('approved_at')
                                    ->whereNull('accepted_at')
                                    ->whereNull('declined_at');
                            });
                    });
            })
            ->filters([
                Filter::make('rank_filter')
                    ->form([
                        Select::make('rank')
                            ->label('Rank')
                            ->options(function () {
                                return collect(Rank::cases())
                                    ->mapWithKeys(fn (Rank $rank) => [$rank->value => $rank->getLabel()])
                                    ->toArray();
                            })
                            ->placeholder('Select a rank'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['rank'])) {
                            $query->where('rank', $data['rank']);
                        }

                        return $query;
                    })
                    ->indicateUsing(fn (array $data): ?string => isset($data['rank']) && $data['rank'] !== ''
                        ? 'Rank: ' . Rank::from($data['rank'])->getLabel()
                        : null),
                Filter::make('requester_name')
                    ->form([
                        TextInput::make('requester_name')
                            ->label('Requester Name')
                            ->placeholder('Enter requester name'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['requester_name'])) {
                            return $query->whereHas('requester', function (Builder $query) use ($data) {
                                $query->where('name', 'like', '%' . $data['requester_name'] . '%');
                            });
                        }

                        return $query;
                    })->indicateUsing(function (array $data): ?string {
                        return isset($data['requester_name']) && $data['requester_name'] !== ''
                            ? 'Requester: ' . $data['requester_name']
                            : null;
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
            MemberRelationManager::class,
            RequesterRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRankActions::route('/'),
            'create' => Pages\CreateRankAction::route('/create'),
            'edit' => Pages\EditRankAction::route('/{record}/edit'),
        ];
    }

    public static function getMemberFormFields(): array
    {
        $min_days_rank_action = config('app.aod.rank.rank_action_min_days');

        $fields = [
            Select::make('member_id')
                ->hiddenOn('edit')
                ->searchable()
                ->getSearchResultsUsing(function (string $search): array {
                    $roleLimits = [
                        'squadLeader' => config('app.aod.rank.max_squad_leader'),
                        'platoonLeader' => config('app.aod.rank.max_platoon_leader'),
                        'divisionLeader' => config('app.aod.rank.max_division_leader'),
                    ];

                    $currentMember = auth()->user()->member;
                    $user = auth()->user();

                    return Member::query()
                        ->where('name', 'like', "%{$search}%")
                        ->where('id', '<>', $currentMember->id)
                        ->when($user->isSquadLeader(),
                            fn (Builder $query) => $query->where('squad_id', $currentMember->squad_id)
                                ->where('rank', '<', $roleLimits['squadLeader'])
                        )
                        ->when($user->isPlatoonLeader(),
                            fn (Builder $query) => $query->where('platoon_id', $currentMember->platoon_id)
                                ->where('rank', '<', $roleLimits['platoonLeader'])
                        )
                        ->when($user->isDivisionLeader(),
                            fn (Builder $query) => $query->where('division_id', $currentMember->division_id)
                                ->where('rank', '<', $roleLimits['divisionLeader'])
                        )
                        ->when($user->isRole('admin'), fn (Builder $query) => $query->where('division_id', '!=', 0)
                        )
                        ->where('rank', '<=', $currentMember->rank->value)
                        ->limit(5)
                        ->get()
                        ->mapWithKeys(fn ($member) => [$member->id => $member->present()->rankName()])
                        ->toArray();

                })
                ->getOptionLabelUsing(fn ($value): ?string => Member::find($value)?->present()->rankName())
                ->allowHtml()
                ->helperText(function () {
                    $user = auth()->user();
                    $append = 'You cannot select yourself or others of greater rank.';

                    $message = match (true) {
                        $user->isSquadLeader() => "Only squad members up to PFC can be selected. {$append}",
                        $user->isPlatoonLeader() => "Only platoon members up to LCpl can be selected. {$append}",
                        $user->isDivisionLeader() => "Only division members up to SGT can be selected. {$append}",
                        default => $append,
                    };

                    return $message;
                })
                ->rules([
                    'required',
                    fn (callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use (
                        $get, $min_days_rank_action
                    ) {
                        $user = auth()->user();
                        $skipRule = ($user->isDivisionLeader() || $user->isRole('admin')) && $get('override_existing');

                        if (! $skipRule) {
                            $exists = RankAction::where('member_id', $value)
                                ->where('created_at', '>=', Carbon::now()->subDays($min_days_rank_action))
                                ->exists();

                            if ($exists) {
                                $fail(sprintf(
                                    'A rank action for this member already exists within the last %s days.',
                                    $min_days_rank_action
                                ));
                            }
                        }
                    },
                ]),
        ];

        if (auth()->user()->isDivisionLeader() || auth()->user()->isRole('admin')) {
            $fields[] = Checkbox::make('override_existing')
                ->label("Override {$min_days_rank_action} Day Rule")
                ->helperText(sprintf(
                    'Check this box to bypass the %d-day restriction for rank actions.',
                    $min_days_rank_action
                ));
        }

        return $fields;
    }

    public static function getRankActionFields(): array
    {
        return [
            Radio::make('action')
                ->label('Rank Action')
                ->options(function (callable $get) {
                    $user = auth()->user();

                    // If the user is a division leader, allow them to choose a rank to promote to.
                    if ($user->isDivisionLeader()) {
                        $promotionLabel = 'Promotion (choose rank to promote to)';
                    } else {
                        $promotionLabel = 'Promotion';
                    }

                    $memberId = $get('member_id');
                    if ($memberId) {
                        $member = Member::find($memberId);
                        if ($member) {
                            $allRanks = Rank::cases();
                            usort($allRanks, fn (Rank $a, Rank $b) => $a->value <=> $b->value);

                            if (! $user->isDivisionLeader()) {
                                $currentIndex = null;
                                foreach ($allRanks as $index => $rank) {
                                    if ($rank->value === $member->rank->value) {
                                        $currentIndex = $index;
                                        break;
                                    }
                                }
                                if ($currentIndex !== null && isset($allRanks[$currentIndex + 1])) {
                                    $nextRank = $allRanks[$currentIndex + 1];
                                    $promotionLabel = 'Promotion (next rank: ' . ucwords($nextRank->getLabel()) . ')';
                                } else {
                                    $promotionLabel = 'Promotion (member is at the highest rank)';
                                }
                            }
                        }
                    }

                    $options = [
                        'promotion' => $promotionLabel,
                    ];

                    // Only permit demotions for admin or division leaders.
                    if ($user->isDivisionLeader() || $user->isRole('admin')) {
                        $options['demotion'] = 'Demotion (choose a new, lower rank)';
                    }

                    return $options;
                })
                ->default('promotion')
                ->reactive(),

            Select::make('demotion_rank')
                ->label('Demote to')
                ->options(function (callable $get) {
                    $member = Member::find($get('member_id'));

                    if (! $member) {
                        return [];
                    }

                    $allRanks = Rank::cases();
                    usort($allRanks, fn (Rank $a, Rank $b) => $a->value <=> $b->value);

                    $options = array_reduce(
                        array_filter($allRanks, fn (Rank $r) => $r->value < $member->rank->value),
                        fn ($acc, Rank $option) => $acc + [$option->value => ucwords($option->getLabel())],
                        []
                    );

                    return $options;
                })
                ->visible(fn (callable $get) => $get('action') === 'demotion')
                ->required(fn (callable $get) => $get('action') === 'demotion'),

            Select::make('promotion_rank')
                ->label('Promote to')
                ->options(function (callable $get) {
                    $member = Member::find($get('member_id'));
                    if (! $member) {
                        return [];
                    }

                    $allRanks = Rank::cases();
                    usort($allRanks, fn (Rank $a, Rank $b) => $a->value <=> $b->value);

                    $options = [];
                    foreach ($allRanks as $rank) {
                        if ($rank->value > $member->rank->value && $rank->value <= Rank::STAFF_SERGEANT->value) {
                            $options[$rank->value] = ucwords($rank->getLabel());
                        }
                    }

                    return $options;
                })
                ->visible(fn (callable $get) => $get('action') === 'promotion' && auth()->user()->isDivisionLeader())
                ->required(fn (callable $get) => $get('action') === 'promotion' && auth()->user()->isDivisionLeader()),

        ];
    }

    public static function getJustificationFormField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('justification')
            ->required()
            ->rows(5)
            ->columnSpanFull();
    }

    public static function getStatusFormField()
    {
        return Forms\Components\ViewField::make('status')
            ->label('status')
            ->view('filament.forms.components.status-badge')
            ->viewData(['record']);
    }
}
