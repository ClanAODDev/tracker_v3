<?php

namespace App\Filament\Mod\Resources;

use App\Filament\Mod\Resources\TransferResource\Pages;
use App\Jobs\UpdateDivisionForMember;
use App\Models\Division;
use App\Models\Transfer;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Parallax\FilamentComments\Tables\Actions\CommentsAction;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Organization';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name')
                    ->sortable()
                    ->icon(fn ($record) => $record->hold_placed_at ? 'heroicon-s-stop' : null)
                    ->iconColor('warning')
                    ->iconPosition(IconPosition::Before),
                Tables\Columns\TextColumn::make('division.name')
                    ->label('To')
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.rank')
                    ->label('Rank')
                    ->sortable()
                    ->toggleable()
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([

                SelectFilter::make('transferring_to')
                    ->label('Xfers To')
                    ->options(Division::active()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['value'])) {
                            return $query->where('division_id', $data['value']);
                        }

                        return $query;
                    })
                    ->default(auth()->user()->division->id),

                SelectFilter::make('transferring_from')
                    ->label('Xfers From')
                    ->options(Division::active()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['value'])) {
                            $divisionId = $data['value'];

                            return $query->whereHas('member', function (Builder $memberQuery) use ($divisionId) {
                                $memberQuery->where('division_id', $divisionId);
                            });
                        }

                        return $query;
                    })
                    ->default(false),

                Filter::make('incomplete')
                    ->label('Incomplete')
                    ->query(function (Builder $query, array $data): Builder {
                        return empty($data)
                            ? $query
                            : $query->whereNull('approved_at');
                    })
                    ->default(),

            ])
            ->actions([
                CommentsAction::make()
                    ->button()
                    ->color('info')
                    ->size('lg')
                    ->visible(fn (
                        Transfer $transfer
                    ) => auth()->user()->canManageTransferCommentsFor($transfer)),

                Tables\Actions\BulkActionGroup::make([

                    Action::make('Hold')
                        ->label('Place Hold')
                        ->color('warning')
                        ->icon('heroicon-s-stop')
                        ->requiresConfirmation()
                        ->action(function (Transfer $record) {
                            $record->update(['hold_placed_at' => now()]);
                        })
                        ->visible(fn (Transfer $record) => ! $record->hold_placed_at && ! $record->approved_at)
                        ->hidden(fn (Transfer $record) => $record->approved_at),

                    Action::make('Remove Hold')
                        ->label('Remove Hold')
                        ->color('warning')
                        ->icon('heroicon-o-stop')
                        ->requiresConfirmation()
                        ->action(function (Transfer $record) {
                            $record->update(['hold_placed_at' => null]);
                        })
                        ->visible(fn (Transfer $record) => $record->hold_placed_at)
                        ->hidden(fn (Transfer $record) => $record->approved_at),

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
                        ->visible(fn (Transfer $record) => ! $record->approved_at && ! $record->hold_placed_at),
                    Tables\Actions\DeleteAction::make()->hidden(fn (Transfer $record) => $record->hold_placed_at),
                ])
                    ->visible(fn (Transfer $record) => $record->canApprove())
                    ->label('Manage'),

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransfers::route('/'),
            'create' => Pages\CreateTransfer::route('/create'),
        ];
    }
}
