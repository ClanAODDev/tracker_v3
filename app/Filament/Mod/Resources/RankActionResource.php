<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource\Pages;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\MemberRelationManager;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\RequesterRelationManager;
use App\Models\Member;
use App\Models\RankAction;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class RankActionResource extends Resource
{
    protected static ?string $model = RankAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Division';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        $pendingCount = static::getModel()::query()
            ->forUser($user)
            ->pending()
            ->count();

        return $pendingCount ? (string) $pendingCount : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make()
                    ->schema([

                        Section::make()
                            ->hiddenOn('create')
                            ->schema([
                                ViewField::make('status')
                                    ->view('filament.forms.components.status-badge')
                                    ->viewData(['record']),
                                Fieldset::make('')->schema([
                                    Placeholder::make('member_name')
                                        ->label('Member Affected')
                                        ->hiddenOn('create')
                                        ->content(fn($record) => $record->member->name),

                                    Placeholder::make('requester_name')
                                        ->label('Recommended By')
                                        ->hiddenOn('create')
                                        ->content(fn($record) => $record->requester->name),

                                    Placeholder::make('requester_name')
                                        ->label('Approved By')
                                        ->visible(fn($record) => $record->approved_at && $record->approver)
                                        ->hiddenOn('create')
                                        ->content(fn($record) => $record->approver->name),
                                ])->columns(3),
                                Select::make('rank')
                                    ->options(Rank::class)
                                    ->label('Recommended Rank')
                                    ->disabledOn('edit'),
                                Fieldset::make('Dates')->schema([
                                    DateTimePicker::make('awarded_at')
                                        ->visible(fn($record
                                        ) => $record->rank->value >= Rank::SERGEANT->value && $record->awarded_at)
                                        ->label('Awarded At')
                                        ->readOnly(),
                                    DateTimePicker::make('approved_at')
                                        ->visible(fn($record) => $record->approved_at)
                                        ->readOnly(),
                                    DateTimePicker::make('created_at')
                                        ->label('Requested At')
                                        ->readOnly(),
                                ])->columns(3),
                                RichEditor::make('justification')
                                    ->required()
                                    ->disabled(function ($record) {
                                        return $record->requester_id !== auth()->user()->member_id;
                                    })
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(2),
                    ])
                    ->columns(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name'),
                Tables\Columns\TextColumn::make('member.division.name')
                    ->sortable()
                    ->visible(fn() => auth()->user()->isRole('admin')),
                Tables\Columns\TextColumn::make('rank')
                    ->sortable()
                    ->badge(),
                Tables\Columns\ViewColumn::make('status')
                    ->toggleable()
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
                $query->with(['member', 'requester']);
                $query->forUser(auth()->user());
            })
            ->filters(
                [
                    Filter::make('rank_filter')
                        ->form([
                            Select::make('rank')
                                ->label('Rank')
                                ->options(function () {
                                    return collect(Rank::cases())
                                        ->mapWithKeys(fn(Rank $rank) => [$rank->value => $rank->getLabel()])
                                        ->toArray();
                                })
                                ->placeholder('Select a rank'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (!empty($data['rank'])) {
                                $query->where('rank', $data['rank']);
                            }

                            return $query;
                        })
                        ->indicateUsing(fn(array $data): ?string => isset($data['rank']) && $data['rank'] !== ''
                            ? 'Rank: '.Rank::from($data['rank'])->getLabel()
                            : null),

                    Filter::make('requester_name')
                        ->form([
                            TextInput::make('requester_name')
                                ->label('Requester Name')
                                ->placeholder('Enter requester name'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            if (!empty($data['requester_name'])) {
                                return $query->whereHas('requester', function (Builder $query) use ($data) {
                                    $query->where('name', 'like', '%'.$data['requester_name'].'%');
                                });
                            }

                            return $query;
                        })
                        ->indicateUsing(function (array $data): ?string {
                            return isset($data['requester_name']) && $data['requester_name'] !== ''
                                ? 'Requester: '.$data['requester_name']
                                : null;
                        }),

                    Tables\Filters\Filter::make('Incomplete')
                        ->query(function (Builder $query, array $data): Builder {
                            return empty($data) ? $query : $query
                                ->where('denied_at', null)
                                ->where(function (Builder $query) {
                                    $query->where('approved_at', null)
                                        ->orWhere('accepted_at', null);
                                });
                        })
                        ->default(),
                ]

            )
            ->actions([
                Tables\Actions\ViewAction::make(),
                CommentsAction::make()->visible(fn(
                    RankAction $action
                ) => auth()->user()->canManageRankActionCommentsFor($action)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
                ->label('Member')
                ->hiddenOn('edit')
                ->searchable()
                ->getSearchResultsUsing(function (string $search): array {
                    $user = auth()->user();

                    return Member::query()
                        ->eligibleForRankAction($user, $search)
                        ->limit(5)
                        ->get()
                        ->mapWithKeys(fn($member) => [
                            $member->id => $member->present()->rankName(),
                        ])
                        ->toArray();

                })
                ->getOptionLabelUsing(fn($value): ?string => Member::find($value)?->present()->rankName())
                ->allowHtml()
                ->helperText(function () {
                    $user = auth()->user();
                    $append = 'You cannot select yourself or others of greater rank.';

                    return match (true) {
                        $user->isSquadLeader() => "Only squad members up to PFC can be selected. {$append}",
                        $user->isPlatoonLeader() => "Only platoon members up to LCpl can be selected. {$append}",
                        $user->isDivisionLeader() => "Only division members up to SGT can be selected. {$append}",
                        default => $append,
                    };
                })
                ->rules([
                    'required',
                    fn(callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use (
                        $get,
                        $min_days_rank_action
                    ) {
                        $user = auth()->user();
                        $skipRule = ($user->isDivisionLeader() || $user->isRole('admin')) && $get('override_existing');

                        if (!$skipRule) {
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
                            usort($allRanks, fn(Rank $a, Rank $b) => $a->value <=> $b->value);

                            if (!$user->isDivisionLeader()) {
                                $currentIndex = null;
                                foreach ($allRanks as $index => $rank) {
                                    if ($rank->value === $member->rank->value) {
                                        $currentIndex = $index;
                                        break;
                                    }
                                }
                                if ($currentIndex !== null && isset($allRanks[$currentIndex + 1])) {
                                    $nextRank = $allRanks[$currentIndex + 1];
                                    $promotionLabel = 'Promotion (next rank: '.ucwords($nextRank->getLabel()).')';
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
                    if (
                        ($user->isDivisionLeader() || $user->isRole('admin'))
                        && isset($member)
                        && $member->rank->value >= Rank::CADET->value
                    ) {
                        $options['demotion'] = 'Demotion (choose a new, lower rank)';
                    }

                    return $options;
                })
                ->default('promotion')
                ->reactive(),

            Select::make('demotion_rank')
                ->label('Demote to')
                ->helperText('Demotions are immediate and do not alert the member.')
                ->options(function (callable $get) {
                    $member = Member::find($get('member_id'));

                    if (!$member) {
                        return [];
                    }

                    $allRanks = Rank::cases();
                    usort($allRanks, fn(Rank $a, Rank $b) => $a->value <=> $b->value);

                    $options = array_reduce(
                        array_filter($allRanks, fn(Rank $r) => $r->value < $member->rank->value),
                        fn($acc, Rank $option) => $acc + [$option->value => ucwords($option->getLabel())],
                        []
                    );

                    return $options;
                })
                ->visible(fn(callable $get) => $get('action') === 'demotion')
                ->required(fn(callable $get) => $get('action') === 'demotion'),

            Select::make('promotion_rank')
                ->label('Promote to')
                ->options(function (callable $get) {
                    $member = Member::find($get('member_id'));
                    if (!$member) {
                        return [];
                    }

                    $allRanks = Rank::cases();
                    usort($allRanks, fn(Rank $a, Rank $b) => $a->value <=> $b->value);

                    $options = [];
                    foreach ($allRanks as $rank) {
                        if ($rank->value > $member->rank->value && $rank->value <= Rank::STAFF_SERGEANT->value) {
                            $options[$rank->value] = ucwords($rank->getLabel());
                        }
                    }

                    return $options;
                })
                ->visible(fn(callable $get) => $get('action') === 'promotion' && auth()->user()->isDivisionLeader())
                ->required(fn(callable $get) => $get('action') === 'promotion' && auth()->user()->isDivisionLeader()),

        ];
    }
}
