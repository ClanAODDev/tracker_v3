<?php

namespace App\Filament\Settings\Pages;

use App\Models\Handle;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IngameHandles extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static string $view = 'filament.settings.pages.profile';

    public ?Member $record = null;

    public array $formData = [];

    public function mount(): void
    {
        $this->record = auth()->user()->member;
        $this->formData['handleGroups'] = $this->getGroupedHandles();
    }

    private function getGroupedHandles(): array
    {
        return $this->record->memberHandles()
            ->with('handle')
            ->get()
            ->groupBy('handle_id')
            ->sortBy(function ($group, $handleId) {
                $handle = Handle::find($handleId);

                return $handle?->label ?? '';
            })
            ->map(function ($group) {
                return [
                    'uuid' => (string) Str::uuid(),
                    'handle_id' => $group->first()->handle_id,
                    'handles' => $group
                        ->sortByDesc('primary') // 🔹 Primary first
                        ->values()
                        ->map(function ($mh) {
                            return [
                                'uuid' => (string) Str::uuid(),
                                'id' => $mh->id,
                                'value' => $mh->value,
                                'primary' => (bool) $mh->primary,
                            ];
                        })
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Save Handles')->extraAttributes([
                'wire:click.prevent' => 'save',
            ])->submit('handles-form'),
        ];
    }

    public function save(): void
    {
        if (! $this->record) {
            Notification::make()
                ->title('Unable to save: No member record found.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->formData['handleGroups'] ?? [];

        // Ensure exactly one primary per handle type
        foreach ($data as &$group) {
            $primaries = collect($group['handles'])->where('primary', true);
            if ($primaries->count() === 0 && ! empty($group['handles'])) {
                $group['handles'][0]['primary'] = true;
            } elseif ($primaries->count() > 1) {
                $firstPrimary = $primaries->keys()->first();
                foreach ($group['handles'] as $i => &$handle) {
                    $handle['primary'] = ($i === $firstPrimary);
                }
            }
        }
        unset($group);

        // Flatten for saving
        $flattened = [];
        foreach ($data as $group) {
            $handleId = $group['handle_id'] ?? null;
            if (! $handleId) {
                continue;
            }

            foreach (($group['handles'] ?? []) as $h) {
                if (empty($h['value'])) {
                    continue;
                }
                $flattened[] = [
                    'id' => $h['id'] ?? null,
                    'handle_id' => $handleId,
                    'value' => $h['value'],
                    'primary' => (bool) $h['primary'],
                ];
            }
        }

        // Delete removed rows
        $existingIds = DB::table('handle_member')
            ->where('member_id', $this->record->id)
            ->pluck('id')
            ->toArray();

        $formIds = collect($flattened)->pluck('id')->filter()->toArray();
        $idsToDelete = array_diff($existingIds, $formIds);

        if (! empty($idsToDelete)) {
            DB::table('handle_member')
                ->where('member_id', $this->record->id)
                ->whereIn('id', $idsToDelete)
                ->delete();
        }

        // Insert or update
        foreach ($flattened as $row) {
            if (! empty($row['id'])) {
                DB::table('handle_member')
                    ->where('id', $row['id'])
                    ->update([
                        'handle_id' => $row['handle_id'],
                        'value' => $row['value'],
                        'primary' => $row['primary'],
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('handle_member')->insert([
                    'member_id' => $this->record->id,
                    'handle_id' => $row['handle_id'],
                    'value' => $row['value'],
                    'primary' => $row['primary'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Notification::make()
            ->title('Handles updated successfully')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('formData')
            ->schema([
                Forms\Components\Section::make('Handles')
                    ->columns(1)
                    ->schema([
                        Repeater::make('handleGroups')
                            ->label('Handle Types')
                            ->collapsible()
                            ->itemLabel(function (array $state) {
                                if (! empty($state['handle_id'])) {
                                    $handle = Handle::find($state['handle_id']);

                                    return $handle?->label ?? 'New Handle Type';
                                }

                                return 'New Handle Type';
                            })
                            ->reorderable(false)
                            ->collapsed()
                            ->defaultItems(1)
                            ->schema([
                                Select::make('handle_id')
                                    ->label('Handle Type')
                                    ->disabled(function (\Filament\Forms\Get $get) {
                                        $handles = $get('handles') ?? [];

                                        // Disable if any existing handle in this group has an ID
                                        return collect($handles)->contains(fn ($h) => ! empty($h['id']));
                                    })
                                    ->options(function (\Filament\Forms\Get $get, \Filament\Forms\Set $set) {
                                        // Get all handle IDs already used in the form
                                        $allGroups = $get('../../handleGroups') ?? [];
                                        $usedHandleIds = collect($allGroups)
                                            ->pluck('handle_id')
                                            ->filter()
                                            ->toArray();

                                        // If we're editing an existing group, allow its own handle_id
                                        $currentHandleId = $get('handle_id');
                                        if ($currentHandleId) {
                                            $usedHandleIds = array_diff($usedHandleIds, [$currentHandleId]);
                                        }

                                        // Return all available handle types except the ones already used
                                        return Handle::orderBy('label')
                                            ->whereNotIn('id', $usedHandleIds)
                                            ->pluck('label', 'id');
                                    })
                                    ->required(),

                                Repeater::make('handles')
                                    ->reorderable(false)
                                    ->label('Handles')
                                    ->defaultItems(1)
                                    ->columns()
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('In-game Handle')
                                            ->required(),

                                        Toggle::make('primary')
                                            ->label('Primary')
                                            ->helperText('Only one primary handle per type is allowed.')
                                            ->reactive()
                                            ->visible(function (\Filament\Forms\Get $get) {
                                                // Only show if this handle already exists (has an ID)
                                                return filled($get('id'));
                                            })
                                            ->disabled(function (\Filament\Forms\Get $get) {
                                                // If this is currently ON and it's the only primary in this group, disable it
                                                if (! $get('primary')) {
                                                    return false;
                                                }

                                                $allHandles = $get('../../handles') ?? [];
                                                $primaryCount = collect($allHandles)->where('primary', true)->count();

                                                return $primaryCount <= 1; // disable if it's the only one
                                            })
                                            ->afterStateUpdated(function (
                                                bool $state,
                                                \Filament\Forms\Get $get,
                                                \Filament\Forms\Set $set
                                            ) {
                                                if (! $state) {
                                                    return; // Only act when turning ON
                                                }

                                                $currentHandleId = $get('handle_id');
                                                $currentUuid = $get('uuid') ?? uniqid();

                                                // Assign UUID if missing
                                                if (! $get('uuid')) {
                                                    $set('uuid', $currentUuid);
                                                }

                                                // Get all handles in the current group
                                                $allHandles = $get('../../handles') ?? [];

                                                // Unset all primaries for the same type, set current as primary
                                                foreach ($allHandles as &$handle) {
                                                    if (($handle['handle_id'] ?? null) === $currentHandleId) {
                                                        $handle['primary'] = (($handle['uuid'] ?? null) === $currentUuid);
                                                    }
                                                }
                                                unset($handle);

                                                // Move the current primary to the top of the list
                                                usort($allHandles, function ($a, $b) {
                                                    return ($b['primary'] ?? false) <=> ($a['primary'] ?? false);
                                                });

                                                $set('../../handles', array_values($allHandles));

                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
