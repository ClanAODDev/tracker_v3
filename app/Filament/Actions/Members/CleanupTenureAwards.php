<?php

namespace App\Filament\Actions\Members;

use App\Data\TenureReconciliation;
use App\Exceptions\TenureReconciliationInProgress;
use App\Models\Member;
use App\Services\TenureAwardService;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class CleanupTenureAwards
{
    /**
     * A header action for use in ListMembers::getHeaderActions()
     */
    public static function make(string $name = 'fixTenureAwards'): Action
    {
        return Action::make($name)
            ->label('Fix Tenure Awards')
            ->schema([
                Toggle::make('persist')
                    ->label('Persist changes')
                    ->helperText('If off, runs a dry-run and only reports the counts.')
                    ->default(false),
                Toggle::make('remove_invalid')
                    ->label('Remove tenure awards the member has not reached')
                    ->helperText('Use with care: members who returned to the clan can legitimately hold awards from earlier tenure.')
                    ->default(false),
            ])
            ->requiresConfirmation()
            ->action(function (array $data, $livewire) {
                /** @var ListRecords $livewire */
                $query = method_exists($livewire, 'getFilteredTableQuery')
                    ? $livewire->getFilteredTableQuery()
                    : Member::query();

                try {
                    $summary = DB::transaction(
                        fn () => app(TenureAwardService::class)->reconcile(
                            $query->whereHas('division'),
                            persist: (bool) ($data['persist'] ?? false),
                            removeInvalid: (bool) ($data['remove_invalid'] ?? false),
                        ),
                        3,
                    );
                } catch (TenureReconciliationInProgress $exception) {
                    Notification::make()->title($exception->getMessage())->warning()->send();

                    return;
                }

                self::notifySummary($summary, (bool) ($data['persist'] ?? false));
            });
    }

    private static function notifySummary(TenureReconciliation $summary, bool $persist): void
    {
        $mode = $persist ? 'Applied' : 'Dry-run';
        $body = implode('<br />', [
            "{$mode} tenure award corrections:",
            '- Missing awards ' . ($persist ? 'added' : 'found') . ": {$summary->missing}",
            '- Invalid awards ' . ($persist ? 'removed' : 'found') . ": {$summary->invalid}",
        ]);

        Notification::make()
            ->title('Tenure Awards – Summary')
            ->body($body)
            ->success()
            ->persistent()
            ->send();
    }
}
