<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\MemberAwardResource\Pages\CreateMemberAward;
use App\Filament\Mod\Resources\MemberAwardResource\Pages\EditMemberAward;
use App\Filament\Mod\Resources\MemberAwardResource\Pages\ListMemberAwards;
use App\Filament\Mod\Resources\MemberAwardResource\RelationManagers\AwardRelationManager;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\RequesterRelationManager;
use App\Models\Award;
use App\Models\Division;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwarded;
use Closure;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MemberAwardResource extends Resource
{
    protected static ?string $model = MemberAward::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|\UnitEnum|null $navigationGroup = 'Division';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if ($user->isRole('admin')) {
            $count = MemberAward::needsApproval()
                ->whereHas('award', fn ($query) => $query->whereNull('division_id'))
                ->count();

            return $count > 0 ? (string) $count : null;
        }

        if ($user->isDivisionLeader()) {
            $count = MemberAward::needsApproval()
                ->whereHas('member', fn ($query) => $query->where('division_id', $user->member->division_id))
                ->count();

            return $count > 0 ? (string) $count : null;
        }

        return null;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->isRole(['admin', 'sr_ldr']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Award Selection')
                    ->columns(2)
                    ->hiddenOn('edit')
                    ->schema([
                        Select::make('division_filter')
                            ->label('Division')
                            ->options(
                                Division::where('active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->prepend('Clan-Wide', 'clan-wide')
                            )
                            ->placeholder('All divisions')
                            ->live()
                            ->dehydrated(false),

                        Select::make('award_id')
                            ->label('Award')
                            ->relationship('award', 'name', function (Builder $query, Get $get) {
                                $divisionFilter = $get('division_filter');
                                if ($divisionFilter === 'clan-wide') {
                                    $query->whereNull('division_id');
                                } elseif ($divisionFilter) {
                                    $query->where('division_id', $divisionFilter);
                                }
                                $query->orderBy('name');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make('Recipient')
                    ->schema([
                        Select::make('member_id')
                            ->label('Member')
                            ->relationship('member', 'name', function (Builder $query) {
                                $query->whereHas('division', fn (Builder $q) => $q->where('active', true))
                                    ->orderBy('name');
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->division?->name})")
                            ->searchable()
                            ->required()
                            ->helperText('Search by member name')
                            ->rules([
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                    $awardId = $get('award_id');
                                    if (! $awardId) {
                                        return;
                                    }
                                    $award = Award::find($awardId);
                                    if (! $award || $award->repeatable) {
                                        return;
                                    }
                                    $query = MemberAward::where('member_id', $value)->where('award_id', $awardId);
                                    if ($record) {
                                        $query->where('id', '!=', $record->id);
                                    }
                                    if ($query->exists()) {
                                        $fail('This member already has this award.');
                                    }
                                },
                            ]),

                        Textarea::make('reason')
                            ->label('Justification')
                            ->required()
                            ->rows(3)
                            ->helperText('Explain why this member should receive this award'),
                    ]),

                Section::make('Options')
                    ->hiddenOn('edit')
                    ->schema([
                        Toggle::make('approved')
                            ->label('Auto-approve')
                            ->helperText('Skip the approval queue and grant immediately')
                            ->default(false)
                            ->visible(fn () => auth()->user()->isRole(['admin', 'sr_ldr'])),
                    ]),

                Section::make('Metadata')
                    ->columns(2)
                    ->hiddenOn(['edit', 'create'])
                    ->schema([
                        DateTimePicker::make('created_at')->default(now()),
                        DateTimePicker::make('updated_at')->default(now()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('award.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member.name')
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Justification')
                    ->toggleable(),

                TextColumn::make('award.division.name')
                    ->label('Division'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters(filters: [
                Filter::make('needs approval')
                    ->query(fn (Builder $query): Builder => $query->where('approved', false))->default(),
                Filter::make('clan_wide')
                    ->label('Clan-Wide Only')
                    ->query(fn (Builder $query): Builder => $query->whereHas('award', fn ($q) => $q->whereNull('division_id'))),
                SelectFilter::make('byDivision')->relationship('award.division', 'name')->label('By Division'),
                SelectFilter::make('award')->relationship('award', 'name'),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    BulkAction::make('approve')
                        ->label('Approve')
                        ->hidden(fn () => ! auth()->user()->isRole(['admin', 'sr_ldr']))
                        ->action(fn (Collection $records) => $records->each->update(['approved' => true])),

                    BulkAction::make('approve_and_notify')
                        ->label('Approve and Notify')
                        ->hidden(fn () => ! auth()->user()->isRole(['admin', 'sr_ldr']))
                        ->action(fn (Collection $records) => $records->each->update(['approved' => true]))
                        ->requiresConfirmation()
                        ->modalDescription('This will generate a notification for every award approved.')
                        ->after(function (Collection $records) {
                            foreach ($records as $memberAward) {
                                if ($memberAward->member->division->settings()->get('chat_alerts.member_awarded')) {
                                    $memberAward->member->division->notify(new NotifyDivisionMemberAwarded(
                                        $memberAward->member->name,
                                        $memberAward->award
                                    ));
                                }
                            }
                        }),

                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AwardRelationManager::class,
            RequesterRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMemberAwards::route('/'),
            'create' => CreateMemberAward::route('/create'),
            'edit'   => EditMemberAward::route('/{record}/edit'),
        ];
    }
}
