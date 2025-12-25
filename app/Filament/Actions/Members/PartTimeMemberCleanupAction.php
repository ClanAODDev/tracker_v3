<?php

namespace App\Filament\Actions\Members;

use App\Models\Member;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class PartTimeMemberCleanupAction
{
    public static function make(string $name = 'partTimeMemberCleanup'): Action
    {
        return Action::make($name)
            ->label('Cleanup Part-Time')
            ->modalHeading('Cleanup Part-Time Division Assignments')
            ->modalDescription('Removes part-time division entries that match a member’s full-time division. Applies to the current filtered list.')
            ->schema([
                Toggle::make('persist')
                    ->label('Persist changes')
                    ->helperText('Off = dry run (just shows counts).')
                    ->default(false),
            ])
            ->requiresConfirmation()
            ->action(function (array $data, $livewire) {
                /** @var ListRecords $livewire */
                $query = method_exists($livewire, 'getFilteredTableQuery')
                    ? $livewire->getFilteredTableQuery()
                    : Member::query();

                /** @var EloquentCollection<int,Member> $members */
                $members = $query
                    ->select(['id', 'clan_id', 'division_id'])
                    ->with(['partTimeDivisions:id'])
                    ->get();

                $summary = DB::transaction(function () use ($members, $data) {
                    return self::runCleanupForMembers($members, (bool) ($data['persist'] ?? false));
                }, 3);

                self::notifySummary($summary, (bool) ($data['persist'] ?? false));
            });
    }

    private static function runCleanupForMembers(iterable $members, bool $persist): array
    {
        $checked = 0;
        $affected = 0;
        $detached = 0;

        foreach ($members as $member) {
            $checked++;

            $fullTimeDivisionId = $member->division_id;
            if (! $fullTimeDivisionId) {
                continue;
            }

            $ptIds = $member->partTimeDivisions->pluck('id')->all();
            if (empty($ptIds)) {
                continue;
            }

            if (in_array($fullTimeDivisionId, $ptIds, true)) {
                $affected++;
                if ($persist) {
                    $member->partTimeDivisions()->detach($fullTimeDivisionId);
                }
                $detached++;
            }
        }

        return compact('checked', 'affected', 'detached');
    }

    private static function notifySummary(array $summary, bool $persist): void
    {
        $mode = $persist ? 'Applied' : 'Dry run';
        $body = implode('<br />', [
            "{$mode} part-time cleanup:",
            "- Assignments scanned: {$summary['checked']}",
            '- Excessive entries ' . ($persist ? 'removed' : 'found') . ": {$summary['detached']}",
        ]);

        Notification::make()
            ->title('Part-Time Division Cleanup')
            ->body($body)
            ->persistent()
            ->success()
            ->send();
    }
}
