<?php

namespace App\Filament\Forms\Components;

use App\Models\Handle;
use App\Models\Member;
use App\Models\MemberHandle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IngameHandlesForm
{
    public static function make(string $statePath = 'handleGroups'): Repeater
    {
        return Repeater::make($statePath)
            ->label('Handle Types')
            ->collapsible()
            ->reorderable(false)
            ->collapsed(function (Get $get) use ($statePath) {
                $allGroups = $get($statePath) ?? [];

                return count($allGroups) > 1;
            })
            ->itemLabel(function (array $state) {
                if (! empty($state['handle_id'])) {
                    $handle = \App\Models\Handle::find($state['handle_id']);

                    return $handle?->label ?? 'New Handle Type';
                }

                return 'New Handle Type';
            })
            ->defaultItems(1)

            ->schema([
                Select::make('handle_id')
                    ->label('Handle Type')
                    ->options(function (Get $get) use ($statePath) {

                        $allGroups = $get("../../{$statePath}}") ?? [];
                        $usedHandleIds = collect($allGroups)
                            ->pluck('handle_id')
                            ->filter()
                            ->toArray();

                        $currentHandleId = $get('handle_id');
                        if ($currentHandleId) {
                            $usedHandleIds = array_diff($usedHandleIds, [$currentHandleId]);
                        }

                        return Handle::orderBy('label')
                            ->whereNotIn('id', $usedHandleIds)
                            ->pluck('label', 'id');
                    })
                    ->required(),

                Repeater::make('handles')
                    ->label('Handles')
                    ->reorderable(false)
                    ->grid()
                    ->defaultItems(1)
                    ->minItems(1)
                    ->columns()
                    ->schema([
                        TextInput::make('value')
                            ->label('In-game Handle')

                            ->required(),

                        Toggle::make('primary')
                            ->label('Primary')
                            ->helperText('Only one primary handle per type is allowed.')
                            ->default(function (Get $get) {
                                $handles = $get('../../handles');

                                return count($handles) === 1; // Default to true if only one handle
                            })
                            ->reactive()
                            ->afterStateUpdated(function (
                                bool $state,
                                Get $get,
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

                                $set('../../handles', array_values($allHandles));
                            }),
                    ]),
            ]);
    }

    public static function getGroupedHandles(Member $member): array
    {
        return $member->memberHandles()
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
                        ->sortByDesc('primary')
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

    public static function saveHandles(Model $member, array $handleGroups): void
    {
        foreach ($handleGroups as &$group) {
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

        $flattened = [];
        foreach ($handleGroups as $group) {
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

        $existingIds = \DB::table('handle_member')
            ->where('member_id', $member->id)
            ->pluck('id')
            ->toArray();

        $formIds = collect($flattened)->pluck('id')->filter()->toArray();
        $idsToDelete = array_diff($existingIds, $formIds);

        if (! empty($idsToDelete)) {
            MemberHandle::where('member_id', $member->id)
                ->whereIn('id', $idsToDelete)
                ->delete();
        }

        foreach ($flattened as $row) {
            if (! empty($row['id'])) {
                MemberHandle::where('id', $row['id'])
                    ->update([
                        'handle_id' => $row['handle_id'],
                        'value' => $row['value'],
                        'primary' => $row['primary'],
                        'updated_at' => now(),
                    ]);
            } else {
                MemberHandle::create([
                    'member_id' => $member->id,
                    'handle_id' => $row['handle_id'],
                    'value' => $row['value'],
                    'primary' => $row['primary'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
