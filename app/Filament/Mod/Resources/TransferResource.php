<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\TransferResource\Pages\CreateTransfer;
use App\Filament\Mod\Resources\TransferResource\Pages\ListTransfers;
use App\Jobs\UpdateDivisionForMember;
use App\Models\Division;
use App\Models\Member;
use App\Models\Transfer;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Organization';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user || ! $user->isDivisionLeader()) {
            return null;
        }

        $divisionId = $user->member?->division_id;

        $count = Transfer::pending()
            ->where(function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId)
                    ->orWhereHas('member', fn ($q) => $q->where('division_id', $divisionId));
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pending transfers involving your division';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->sortable()
                    ->icon(fn ($record) => $record->hold_placed_at ? 'heroicon-s-stop' : null)
                    ->iconColor('warning')
                    ->iconPosition(IconPosition::Before),
                TextColumn::make('division.name')
                    ->label('To')
                    ->sortable(),
                TextColumn::make('member.rank')
                    ->label('Rank')
                    ->sortable()
                    ->toggleable()
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Requested')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(function (Transfer $record): string {
                        if ($record->approved_at) {
                            return 'Approved';
                        }
                        if ($record->hold_placed_at) {
                            return 'On Hold';
                        }

                        return 'Awaiting ' . strtoupper($record->division->abbreviation) . ' approval';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Approved') => 'success',
                        str_contains($state, 'Hold') => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('member')
                    ->label('Member')
                    ->searchable()
                    ->options(fn () => Member::whereHas('transfers')->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['value'])) {
                            return $query->where('member_id', $data['value']);
                        }

                        return $query;
                    }),

                SelectFilter::make('transferring_to')
                    ->label('Xfers To')
                    ->searchable()
                    ->options(Division::active()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['value'])) {
                            return $query->where('division_id', $data['value']);
                        }

                        return $query;
                    })
                    ->default(auth()->user()->division->id),

                Filter::make('incomplete')
                    ->label('Incomplete')
                    ->query(function (Builder $query, array $data): Builder {
                        return empty($data)
                            ? $query
                            : $query->whereNull('approved_at');
                    })
                    ->default(),

            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                CommentsAction::make()
                    ->button()
                    ->color('info')
                    ->size('lg')
                    ->visible(fn (
                        Transfer $transfer
                    ) => auth()->user()->canManageTransferCommentsFor($transfer)),

                ActionGroup::make([
                    Action::make('Hold')
                        ->label('Place Hold')
                        ->color('warning')
                        ->icon('heroicon-s-stop')
                        ->requiresConfirmation()
                        ->action(function (Transfer $record) {
                            $record->update(['hold_placed_at' => now()]);
                        })
                        ->visible(fn (Transfer $record) => ! $record->hold_placed_at && ! $record->approved_at),

                    Action::make('Remove Hold')
                        ->label('Remove Hold')
                        ->color('warning')
                        ->icon('heroicon-o-stop')
                        ->requiresConfirmation()
                        ->action(function (Transfer $record) {
                            $record->update(['hold_placed_at' => null]);
                        })
                        ->visible(fn (Transfer $record) => $record->hold_placed_at && ! $record->approved_at),

                    Action::make('Approve')
                        ->label('Approve')
                        ->color('success')
                        ->modalHeading('Approve transfer')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(function (Transfer $record) {
                            $record->approve();
                            UpdateDivisionForMember::dispatch($record);
                        })
                        ->visible(fn (Transfer $record) => $record->canApprove() && ! $record->approved_at && ! $record->hold_placed_at),

                    DeleteAction::make()
                        ->visible(fn (Transfer $record) => ! $record->hold_placed_at && ! $record->approved_at),
                ])
                    ->visible(fn (Transfer $record) => auth()->user()->can('hold', $record) || $record->canApprove())
                    ->label('Manage')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isRole('admin')),
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
            'index' => ListTransfers::route('/'),
            'create' => CreateTransfer::route('/create'),
        ];
    }
}
