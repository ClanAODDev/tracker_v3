<?php

namespace App\Filament\Admin\Resources\PendingDiscordResource\Pages;

use App\Filament\Admin\Resources\PendingDiscordResource;
use App\Jobs\PurgePendingDiscordRegistrations;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPendingDiscord extends ListRecords
{
    protected static string $resource = PendingDiscordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('purge_old')
                ->label('Purge Old Accounts')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Purge Old Pending Registrations')
                ->modalDescription('This will permanently delete all pending Discord registrations older than 60 days. This action cannot be undone.')
                ->action(function () {
                    PurgePendingDiscordRegistrations::dispatch();

                    Notification::make()
                        ->title('Purge job queued')
                        ->body('Old pending registrations will be removed shortly.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
