<?php

namespace App\Filament\Mod\Widgets;

use App\Enums\Rank;
use App\Models\Division;
use App\Models\Member;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RankDistributionTableWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Rank Breakdown';

    public function getTableRecordKey($record): string
    {
        return (string) $record->rank->value;
    }

    public function table(Table $table): Table
    {
        $division = $this->getDivision();

        $rankCounts = $division
            ? Member::where('division_id', $division->id)
                ->selectRaw('rank, COUNT(*) as member_count')
                ->groupBy('rank')
                ->orderByDesc('member_count')
                ->get()
            : collect();

        return $table
            ->query(
                Member::query()
                    ->selectRaw('rank, COUNT(*) as member_count')
                    ->where('division_id', $division?->id)
                    ->groupBy('rank')
                    ->orderByDesc('member_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->getColor()),

                Tables\Columns\TextColumn::make('member_count')
                    ->label('Members')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewMembers')
                    ->label('View')
                    ->icon('heroicon-o-users')
                    ->modalHeading(fn ($record) => 'Members with rank: ' . Rank::from($record->rank->value)->getLabel())
                    ->modalContent(function ($record) {
                        $division = $this->getDivision();
                        $members = Member::where('division_id', $division->id)
                            ->where('rank', $record->rank->value)
                            ->orderBy('name')
                            ->get();

                        return view('filament.mod.widgets.rank-members-modal', [
                            'members' => $members,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->paginated(false)
            ->emptyStateHeading('No members found')
            ->emptyStateDescription('No members in your division.');
    }

    protected function getDivision(): ?Division
    {
        return Auth::user()?->division;
    }
}
