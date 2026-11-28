<?php

namespace App\Filament\Mod\Resources\TransferResource\Pages;

use App\Enums\Rank;
use App\Filament\Concerns\ImportsDatedHistory;
use App\Filament\Mod\Resources\TransferResource;
use App\Models\Division;
use App\Models\Member;
use App\Models\Transfer;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportTransferHistory extends CreateRecord
{
    use ImportsDatedHistory;

    protected static string $resource = TransferResource::class;

    protected static ?string $title = 'Import Transfer History';

    protected static ?string $breadcrumb = 'Import History';

    private ?Collection $divisionLookup = null;

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

                            $set('entries', Transfer::where('member_id', $state)
                                ->whereNotNull('approved_at')
                                ->orderBy('created_at')
                                ->get()
                                ->map(fn (Transfer $transfer) => [
                                    'division' => (string) $transfer->division_id,
                                    'date'     => $transfer->created_at->toDateString(),
                                ])
                                ->all());
                        })
                        ->getSearchResultsUsing(fn (string $search): array => Member::query()
                            ->where('name', 'like', "%{$search}%")
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(fn ($member) => [$member->id => $member->present()->rankName()])
                            ->toArray()
                        )
                        ->getOptionLabelUsing(fn ($value): ?string => Member::find($value)?->present()->rankName())
                        ->allowHtml()
                        ->required(),
                ]),

            $this->historyImportSection(
                'Two columns per row: division (name, abbreviation or slug) and the date the member joined it (YYYY-MM-DD, yesterday or earlier). A first row that is not a division, such as a header, is ignored.',
                "Battlefield,2019-03-01\nWorld of Warships,2021-07-15",
            ),

            Section::make('Entries')
                ->columnSpanFull()
                ->description('Each row records the member joining a division on that date. Review before submitting; the member\'s current division is not changed.')
                ->schema([
                    Repeater::make('entries')
                        ->hiddenLabel()
                        ->schema([
                            Select::make('division')
                                ->options(fn () => Division::query()
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (Division $division) => [(string) $division->id => $division->active ? $division->name : "{$division->name} (inactive)"])
                                    ->all())
                                ->searchable()
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
        return 'division';
    }

    protected function historyNoun(): string
    {
        return 'division';
    }

    protected function matchHistoryValue(string $input): ?string
    {
        $this->divisionLookup ??= Division::query()->get(['id', 'name', 'abbreviation', 'slug']);
        $needle = Str::lower(trim($input));

        if ($needle === '') {
            return null;
        }

        $match = $this->divisionLookup->first(fn (Division $division) => in_array($needle, [
            Str::lower($division->name),
            Str::lower((string) $division->abbreviation),
            Str::lower((string) $division->slug),
        ], true));

        return $match ? (string) $match->id : null;
    }

    public function create(bool $another = false): void
    {
        $data    = $this->form->getState();
        $member  = Member::findOrFail($data['member_id']);
        $user    = auth()->user();
        $count   = 0;
        $skipped = 0;

        foreach ($data['entries'] ?? [] as $entry) {
            if (empty($entry['date']) || empty($entry['division'])) {
                continue;
            }

            $date       = Carbon::parse($entry['date'])->startOfDay();
            $divisionId = (int) $entry['division'];

            $exists = Transfer::where('member_id', $member->id)
                ->where('division_id', $divisionId)
                ->whereDate('created_at', $date->toDateString())
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            Transfer::withoutTimestamps(fn () => Transfer::create([
                'member_id'   => $member->id,
                'division_id' => $divisionId,
                'approved_at' => $date,
                'approved_by' => $user->id,
                'created_at'  => $date,
                'updated_at'  => $date,
            ]));

            $count++;
        }

        Notification::make()
            ->title("Imported {$count} " . Str::plural('transfer', $count) . " for {$member->name}")
            ->body($skipped > 0 ? "{$skipped} already on record were skipped." : null)
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
                ->url(route('transfer-history.template')),
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
