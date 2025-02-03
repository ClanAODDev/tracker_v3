<?php

namespace App\Filament\Mod\Resources;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource\Pages;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\MemberRelationManager;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\RequesterRelationManager;
use App\Models\RankAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;

class RankActionResource extends Resource
{
    protected static ?string $model = RankAction::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Division';

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function getAllowedRanks($user)
    {
        $maxRankValues = [];
        if ($user->isSquadLeader()) {
            $maxRankValues[] = Rank::SPECIALIST->value;
        }

        if ($user->isPlatoonLeader()) {
            $maxRankValues[] = Rank::CORPORAL->value;
        }

        if ($user->isDivisionLeader()) {
            $maxRankValues[] = Rank::STAFF_SERGEANT->value;
        }

        $maxAllowedValue = ! empty($maxRankValues) ? min($maxRankValues) : null;

        $ranks = Rank::cases();

        if ($maxAllowedValue !== null) {
            $ranks = array_filter($ranks, function (Rank $rank) use ($maxAllowedValue) {
                return $rank->value <= $maxAllowedValue;
            });
        }

        return $ranks;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rank Action Details')
                    ->hiddenOn('create')
                    ->schema([
                        static::getMemberFormField(),
                        static::getRankFormField(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                $member = $user->member;

                $query->whereHas('member', function (Builder $memberQuery) use ($user, $member) {
                    if ($user->isPlatoonLeader()) {
                        $memberQuery->where('platoon_id', $member->platoon_id);
                    }
                    if ($user->isSquadLeader()) {
                        $memberQuery->where('squad_id', $member->squad_id);
                    }
                    if ($user->isDivisionLeader()) {
                        $memberQuery->where('division_id', $member->division_id);
                    }
                });
            })
            ->filters([
                Tables\Filters\Filter::make('needs approval')
                    ->query(fn (Builder $query): Builder => $query->where('approved_at', null))->default(),
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
            RequesterRelationManager::class,
            MemberRelationManager::class,
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

    public static function getMemberFormField(): Select
    {
        return Forms\Components\Select::make('member_id')
            ->hiddenOn('edit')
            ->relationship('member', 'name', function (Builder $query) {

                $query->whereNot('id', auth()->user()->member->id);

                if (auth()->user()->isSquadLeader()) {
                    $query->where('squad_id', auth()->user()->member->squad_id);
                } else {
                    if (auth()->user()->isPlatoonLeader()) {
                        $query->where('platoon_id', auth()->user()->member->platoon_id);
                    } elseif (auth()->user()->isDivisionLeader()) {
                        $query->where('division_id', auth()->user()->member->division_id);
                    }
                }
            })
            ->allowHtml()
            ->helperText(function () {
                $user = auth()->user();
                $append = 'You cannot select yourself.';

                $message = match (true) {
                    $user->isSquadLeader() => "Only squad members can be selected. {$append}",
                    $user->isPlatoonLeader() => "Only platoon members can be selected. {$append}",
                    $user->isDivisionLeader() => "Only division members can be selected. {$append}",
                    default => $append,
                };

                return $message;
            })
            ->required();
    }

    public static function getRankFormField(): Select
    {
        return Select::make('rank')
            ->required()
            ->label('Rank')
            ->options(function () {
                $allowedRanks = self::getAllowedRanks(auth()->user());

                return collect($allowedRanks)
                    ->mapWithKeys(function (Rank $rank) {
                        return [$rank->value => ucwords(str_replace('_', ' ', $rank->getLabel()))];
                    })
                    ->toArray();
            })
            ->helperText(function () {
                $user = auth()->user();

                $maxRank = match (true) {
                    $user->isPlatoonLeader() => Rank::tryFrom(
                        $user->division?->settings()?->get('max_platoon_leader_rank')
                    ),
                    $user->isDivisionLeader() => Rank::CORPORAL,
                    default => null,
                };

                if ($maxRank !== null) {
                    return new HtmlString(
                        sprintf('Promotions automatic up to <strong>%s</strong>.', $maxRank->getLabel())
                    );
                }
            })
            ->rules(function () {
                $allowedRanks = self::getAllowedRanks(auth()->user());

                $allowedValues = collect($allowedRanks)
                    ->map(fn (Rank $rank) => $rank->value)
                    ->toArray();

                return [Rule::in($allowedValues)];
            });
    }

    public static function getJustificationFormField(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('justification')
            ->required()
            ->rows(5)
            ->columnSpanFull();
    }
}
