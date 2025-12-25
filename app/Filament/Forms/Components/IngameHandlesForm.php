<?php

namespace App\Filament\Forms\Components;

use App\Models\Handle;
use App\Models\Member;
use App\Models\MemberHandle;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;

class IngameHandlesForm
{
    public static function make(string $statePath = 'handleGroups'): Repeater
    {
        return Repeater::make($statePath)
            ->label('')
            ->addActionLabel('Add Handle')
            ->reorderable(false)
            ->columns(2)
            ->defaultItems(0)
            ->itemLabel(function (array $state) {
                $handle = null;
                if (! empty($state['handle_id'])) {
                    $handle = Handle::find($state['handle_id']);
                }

                return $handle?->label ?? 'New Handle';
            })
            ->schema([
                Select::make('handle_id')
                    ->label('Game / Platform')
                    ->placeholder('Select a game or platform...')
                    ->options(Handle::orderBy('label')->pluck('label', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('value')
                    ->label('Your Handle / Username')
                    ->placeholder('Enter your in-game name...')
                    ->required(),

                Checkbox::make('primary')
                    ->label('Primary handle for this game')
                    ->helperText('Check this if you have multiple handles for the same game and want this one shown by default'),
            ]);
    }

    public static function getGroupedHandles(Member $member): array
    {
        return $member->memberHandles()
            ->with('handle')
            ->orderBy('handle_id')
            ->get()
            ->map(function ($mh) {
                return [
                    'id' => $mh->id,
                    'handle_id' => $mh->handle_id,
                    'value' => $mh->value,
                    'primary' => (bool) $mh->primary,
                ];
            })
            ->toArray();
    }

    public static function saveHandles(Model $member, array $handles): void
    {
        $existingIds = MemberHandle::where('member_id', $member->id)
            ->pluck('id')
            ->toArray();

        $formIds = collect($handles)->pluck('id')->filter()->toArray();
        $idsToDelete = array_diff($existingIds, $formIds);

        if (! empty($idsToDelete)) {
            MemberHandle::where('member_id', $member->id)
                ->whereIn('id', $idsToDelete)
                ->delete();
        }

        $handlesByType = collect($handles)->groupBy('handle_id');

        foreach ($handlesByType as $handleId => $handlesOfType) {
            $primaryAssigned = false;

            foreach ($handlesOfType as $index => $row) {
                if (empty($row['handle_id']) || empty($row['value'])) {
                    continue;
                }

                $isPrimary = false;
                if (! $primaryAssigned) {
                    if ($row['primary'] ?? false) {
                        $isPrimary = true;
                        $primaryAssigned = true;
                    } elseif ($index === $handlesOfType->keys()->last()) {
                        $isPrimary = true;
                    }
                }

                if (! empty($row['id'])) {
                    MemberHandle::where('id', $row['id'])
                        ->update([
                            'handle_id' => $row['handle_id'],
                            'value' => $row['value'],
                            'primary' => $isPrimary,
                            'updated_at' => now(),
                        ]);
                } else {
                    MemberHandle::create([
                        'member_id' => $member->id,
                        'handle_id' => $row['handle_id'],
                        'value' => $row['value'],
                        'primary' => $isPrimary,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
