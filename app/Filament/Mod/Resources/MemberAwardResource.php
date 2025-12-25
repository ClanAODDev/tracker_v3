<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\MemberAwardResource\Pages\CreateMemberAward;
use App\Filament\Mod\Resources\MemberAwardResource\Pages\EditMemberAward;
use App\Filament\Mod\Resources\MemberAwardResource\Pages\ListMemberAwards;
use App\Filament\Mod\Resources\MemberAwardResource\RelationManagers\AwardRelationManager;
use App\Filament\Mod\Resources\RankActionResource\RelationManagers\RequesterRelationManager;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwarded;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
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
        if (auth()->user()->isDivisionLeader()) {
            $count = MemberAward::needsApproval()
                ->whereHas('member', fn ($query) => $query->where('division_id', auth()->user()->division->id))
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
            ->components([
                Select::make('award_id')
                    ->relationship('award', 'name')
                    ->required()
                    ->hiddenOn('edit'),

                Select::make('member_id')
                    ->relationship('member', 'name', function (Builder $query) {
                        $query->whereHas('division', function (Builder $subQuery) {
                            $subQuery->where('active', true);
                        });
                    })
                    ->columnSpanFull()
                    ->searchable()
                    ->required(),

                Textarea::make('reason')
                    ->required()
                    ->columnSpanFull()
                    ->rows(5),

                Section::make('Metadata')->schema([
                    DateTimePicker::make('created_at')->default(now()),
                    DateTimePicker::make('updated_at')->default(now()),
                ])->columns()->hiddenOn(['edit', 'create']),

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
                SelectFilter::make('by division')->relationship('award.division', 'name'),
                SelectFilter::make('award')->relationship('award', 'name'),
            ])
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
            'index' => ListMemberAwards::route('/'),
            'create' => CreateMemberAward::route('/create'),
            'edit' => EditMemberAward::route('/{record}/edit'),
        ];
    }
}
