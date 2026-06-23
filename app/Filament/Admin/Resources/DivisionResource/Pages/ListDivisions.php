<?php

namespace App\Filament\Admin\Resources\DivisionResource\Pages;

use App\Filament\Admin\Resources\DivisionResource;
use App\Jobs\SyncDivisionDns;
use App\Services\CloudflareDnsService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

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
                ->modalDescription(fn (CloudflareDnsService $service) => buildDnsPreview($service))
                ->action(function () {
                    SyncDivisionDns::dispatch();
                    Notification::make()->title('DNS sync queued')->success()->send();
                }),
            CreateAction::make(),
        ];
    }
}
