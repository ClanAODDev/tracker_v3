<?php

namespace App\Filament\Forms\Components;

use App\Models\Handle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;

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
}
