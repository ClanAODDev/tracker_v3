<?php

namespace App\Filament\Admin\Resources\DivisionResource\Pages;

use App\Filament\Admin\Resources\DivisionResource;
use App\Jobs\SyncDivisionDns;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDivisions extends ListRecords
{
    protected static string $resource = DivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_dns')
                ->label('Sync DNS')
                ->icon('heroicon-o-globe-alt')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Sync Division DNS')
                ->modalDescription('This will create missing CNAMEs and remove stale ones from Cloudflare. Protected records will never be deleted.')
                ->action(function () {
                    SyncDivisionDns::dispatch();
                    Notification::make()->title('DNS sync queued')->success()->send();
                }),
            CreateAction::make(),
        ];
    }
}
