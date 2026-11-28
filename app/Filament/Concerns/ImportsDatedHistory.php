<?php

namespace App\Filament\Concerns;

use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ImportsDatedHistory
{
    abstract protected function historyField(): string;

    abstract protected function historyNoun(): string;

    abstract protected function matchHistoryValue(string $input): ?string;

    protected function historyImportSection(string $description, string $placeholder): Section
    {
        return Section::make('CSV Import')
            ->columnSpanFull()
            ->description($description)
            ->schema([
                FileUpload::make('csv_file')
                    ->label('Upload CSV')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                    ->live()
                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                        $file = is_array($state) ? collect($state)->first() : $state;

                        if (! $file instanceof TemporaryUploadedFile) {
                            return;
                        }

                        $content = file_get_contents($file->getRealPath());

                        if ($content) {
                            $this->addHistoryRows($content, $get, $set);
                        }
                    }),

                Textarea::make('csv_paste')
                    ->label('Paste CSV')
                    ->placeholder($placeholder)
                    ->helperText('Paste or type rows, then select Add rows. Rows already in the list are skipped.')
                    ->rows(6),

                Actions::make([
                    Action::make('addPastedRows')
                        ->label('Add rows')
                        ->icon('heroicon-m-plus')
                        ->action(function (Get $get, Set $set): void {
                            $content = trim((string) $get('csv_paste'));

                            if ($content === '') {
                                Notification::make()->title('Nothing to add')->body('Paste or type some rows first.')->warning()->send();

                                return;
                            }

                            $this->addHistoryRows($content, $get, $set);
                            $set('csv_paste', null);
                        }),
                ])->key('history_paste_actions'),
            ]);
    }

    protected function addHistoryRows(string $content, Get $get, Set $set): void
    {
        $field   = $this->historyField();
        $noun    = $this->historyNoun();
        $parsed  = [];
        $skipped = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($content)) as $index => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $parts     = str_getcsv($line);
            $valueText = trim($parts[0] ?? '');
            $dateText  = trim($parts[1] ?? '');
            $value     = $this->matchHistoryValue($valueText);

            if ($value === null) {
                if ($index > 0 || ! empty($parsed)) {
                    $skipped[] = "\"{$line}\" (unrecognized {$noun})";
                }

                continue;
            }

            if ($dateText === '') {
                $skipped[] = "\"{$line}\" (missing date)";

                continue;
            }

            try {
                $date = Carbon::parse($dateText);
            } catch (Exception) {
                $skipped[] = "\"{$line}\" (invalid date)";

                continue;
            }

            if ($date->isAfter(now()->subDay()->endOfDay())) {
                $skipped[] = "\"{$line}\" (date must be yesterday or earlier)";

                continue;
            }

            $parsed[] = [$field => $value, 'date' => $date->format('Y-m-d')];
        }

        $existing = collect($get('entries') ?? [])
            ->filter(fn ($entry) => ! empty($entry[$field]) && ! empty($entry['date']))
            ->values();

        $seen      = $existing->map(fn ($entry) => $entry[$field] . '|' . $entry['date'])->flip();
        $duplicate = 0;
        $added     = [];

        foreach ($parsed as $entry) {
            $key = $entry[$field] . '|' . $entry['date'];

            if ($seen->has($key)) {
                $duplicate++;

                continue;
            }

            $seen[$key] = true;
            $added[]    = $entry;
        }

        $set('entries', $existing->concat($added)->sortBy('date')->values()->all());

        if ($added !== [] || $duplicate > 0) {
            Notification::make()
                ->title('Added ' . count($added) . ' ' . Str::plural('row', count($added)))
                ->body(match (true) {
                    $duplicate === 1 => '1 row was already in the list, so it was skipped.',
                    $duplicate > 1   => "{$duplicate} rows were already in the list, so they were skipped.",
                    default          => null,
                })
                ->success()
                ->send();
        }

        if ($skipped !== []) {
            Notification::make()
                ->title(count($skipped) . ' ' . Str::plural('row', count($skipped)) . ' skipped')
                ->body(new HtmlString(implode('<br>', array_map('e', $skipped))))
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
