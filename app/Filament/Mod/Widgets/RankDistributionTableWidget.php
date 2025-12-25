<?php

namespace App\Filament\Mod\Widgets;

use App\Models\Division;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RankDistributionTableWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Rank Breakdown';

    public function table(Table $table): Table
    {
        $division = $this->getDivision();
        $divisionId = $division?->id;

        return $table
            ->query(
                Member::query()
                    ->select('rank', DB::raw('COUNT(*) as member_count'), DB::raw('MIN(id) as id'))
                    ->where('division_id', $divisionId)
                    ->groupBy('rank')
                    ->orderByDesc('member_count')
            )
            ->columns([
                TextColumn::make('rank')
                    ->label('Rank')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->getColor()),

                TextColumn::make('member_count')
                    ->label('Members')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
            ])
            ->recordActions([
                Action::make('viewMembers')
                    ->label('View')
                    ->icon('heroicon-o-users')
                    ->modalHeading(fn ($record) => 'Members with rank: ' . $record->rank->getLabel())
                    ->modalContent(function ($record) use ($divisionId) {
                        $members = Member::where('division_id', $divisionId)
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
