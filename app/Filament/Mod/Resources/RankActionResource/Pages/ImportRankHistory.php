<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Enums\Rank;
use App\Filament\Concerns\ImportsDatedHistory;
use App\Filament\Mod\Resources\RankActionResource;
use App\Models\Member;
use App\Models\RankAction;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ImportRankHistory extends CreateRecord
{
    use ImportsDatedHistory;

    protected static string $resource = RankActionResource::class;

    protected static ?string $title = 'Import Rank History';

    protected static ?string $breadcrumb = 'Import History';

    protected function authorizeAccess(): void
    {
        abort_unless(
            auth()->user()?->member?->rank->value >= Rank::MASTER_SERGEANT->value,
            403
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Member')
                ->columnSpanFull()
                ->schema([
                    Select::make('member_id')
                        ->label('Member')
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (! $state) {
                                return;
                            }

                            $existing = RankAction::where('member_id', $state)
                                ->approvedAndAccepted()
                                ->orderBy('accepted_at')
                                ->get()
                                ->map(fn (RankAction $action) => [
                                    'rank' => (string) $action->rank->value,
                                    'date' => $action->accepted_at->toDateString(),
                                ])
                                ->toArray();

                            $set('entries', $existing);
                        })
                        ->getSearchResultsUsing(fn (string $search): array => Member::query()
                            ->where('name', 'like', "%{$search}%")
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($m) => [$m->id => $m->present()->rankName()])
                            ->toArray()
                        )
                        ->getOptionLabelUsing(fn ($value): ?string => Member::find($value)?->present()->rankName())
                        ->allowHtml()
                        ->required(),
                ]),

            $this->historyImportSection(
                'Two columns per row: rank (full name or abbreviation) and date (YYYY-MM-DD, yesterday or earlier). A first row that is not a rank, such as a header, is ignored.',
                "Private,2023-01-15\nCorporal,2023-06-01",
            ),

            Section::make('Entries')
                ->columnSpanFull()
                ->description('Review entries before submitting. Rows are created exactly as shown — no deduplication or ordering is enforced.')
                ->schema([
                    Repeater::make('entries')
                        ->hiddenLabel()
                        ->schema([
                            Select::make('rank')
                                ->options(
                                    collect(Rank::cases())
                                        ->sortBy(fn ($r) => $r->value)
                                        ->mapWithKeys(fn (Rank $r) => [(string) $r->value => $r->getLabel()])
                                        ->toArray()
                                )
                                ->required(),
                            DatePicker::make('date')
                                ->maxDate(now()->subDay()->toDateString())
                                ->required(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->addActionLabel('Add Entry')
                        ->required()
                        ->minItems(1),
                ]),
        ]);
    }

    protected function historyField(): string
    {
        return 'rank';
    }

    protected function historyNoun(): string
    {
        return 'rank';
    }

    protected function matchHistoryValue(string $input): ?string
    {
        foreach (Rank::cases() as $case) {
            if (strcasecmp($case->getLabel(), $input) === 0 || strcasecmp($case->getAbbreviation(), $input) === 0) {
                return (string) $case->value;
            }
        }

        return null;
    }

    public function create(bool $another = false): void
    {
        $data    = $this->form->getState();
        $member  = Member::findOrFail($data['member_id']);
        $user    = auth()->user();
        $entries = $data['entries'] ?? [];
        $count   = 0;

        foreach ($entries as $entry) {
            if (empty($entry['date']) || empty($entry['rank'])) {
                continue;
            }

            $date = Carbon::parse($entry['date']);
            $rank = Rank::from((int) $entry['rank']);

            $exists = RankAction::where('member_id', $member->id)
                ->whereDate('accepted_at', $date->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            RankAction::withoutTimestamps(function () use ($member, $user, $rank, $date) {
                RankAction::create([
                    'member_id'     => $member->id,
                    'requester_id'  => $user->member_id,
                    'approver_id'   => $user->member_id,
                    'rank'          => $rank->value,
                    'justification' => 'Historical entry',
                    'approved_at'   => $date,
                    'accepted_at'   => $date,
                    'awarded_at'    => $rank->isOfficer() ? $date : null,
                    'created_at'    => $date,
                    'updated_at'    => $date,
                ]);
            });

            $count++;
        }

        Notification::make()
            ->title("Imported {$count} " . Str::plural('entry', $count) . " for {$member->name}")
            ->success()
            ->send();

        $this->redirect($this->getResourceUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('rank-history.template')),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Import History')
                ->submit('create'),
            Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResourceUrl('index'))
                ->color('gray'),
        ];
    }
}
