<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Enums\Rank;
use App\Filament\Mod\Resources\RankActionResource;
use App\Models\Member;
use App\Models\RankAction;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportRankHistory extends CreateRecord
{
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

            Section::make('CSV Import')
                ->columnSpanFull()
                ->description('Two columns per row: rank (full name or abbreviation) and date (YYYY-MM-DD). A header row is automatically skipped if the first cell does not match a known rank.')
                ->schema([
                    FileUpload::make('csv_file')
                        ->label('Upload CSV')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $file = is_array($state) ? collect($state)->first() : $state;

                            if (! $file instanceof TemporaryUploadedFile) {
                                return;
                            }

                            $content = file_get_contents($file->getRealPath());
                            if (! $content) {
                                return;
                            }

                            $this->applyParsedContent($content, $set, $get);
                        }),

                    Textarea::make('csv_paste')
                        ->label('Paste CSV')
                        ->placeholder("Private,2023-01-15\nCorporal,2023-06-01")
                        ->rows(6)
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (empty(trim((string) $state))) {
                                return;
                            }

                            $this->applyParsedContent($state, $set, $get);
                        }),
                ]),

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

    private function applyParsedContent(string $content, callable $set, callable $get): void
    {
        $entries = [];
        $skipped = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($content)) as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts     = str_getcsv($line);
            $rankInput = trim($parts[0] ?? '');
            $dateInput = trim($parts[1] ?? '');

            $matched = null;
            foreach (Rank::cases() as $case) {
                if (
                    strcasecmp($case->getLabel(), $rankInput) === 0 ||
                    strcasecmp($case->getAbbreviation(), $rankInput) === 0
                ) {
                    $matched = $case;
                    break;
                }
            }

            if ($matched === null) {
                $skipped[] = "\"{$line}\" (unrecognized rank)";

                continue;
            }

            if (empty($dateInput)) {
                $skipped[] = "\"{$line}\" (missing date)";

                continue;
            }

            try {
                $entries[] = [
                    'rank' => (string) $matched->value,
                    'date' => Carbon::parse($dateInput)->format('Y-m-d'),
                ];
            } catch (\Exception) {
                $skipped[] = "\"{$line}\" (invalid date)";
            }
        }

        $existing = collect($get('entries') ?? [])
            ->filter(fn ($e) => ! empty($e['rank']) && ! empty($e['date']))
            ->values()
            ->toArray();

        $merged = collect(array_merge($existing, $entries))
            ->sortBy('date')
            ->values()
            ->toArray();

        $set('entries', $merged);

        if (! empty($skipped)) {
            $count = count($skipped);
            $label = Str::plural('row', $count);
            $list  = implode('<br>', array_map('e', $skipped));

            Notification::make()
                ->title("{$count} {$label} skipped")
                ->body(new HtmlString($list))
                ->warning()
                ->persistent()
                ->send();
        }
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
