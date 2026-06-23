<?php

namespace App\Filament\Admin\Resources\DivisionResource\Pages;

use App\Filament\Admin\Resources\DivisionResource;
use App\Jobs\SyncDivisionDns;
use App\Services\CloudflareDnsService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

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
                ->modalSubmitActionLabel('Sync Now')
                ->action(function (CloudflareDnsService $service) {
                    try {
                        $result = (new SyncDivisionDns)->handle($service);
                        $body   = collect([
                            $result['created'] ? 'Created: ' . implode(', ', $result['created']) : null,
                            $result['deleted'] ? 'Deleted: ' . implode(', ', $result['deleted']) : null,
                        ])->filter()->join(' · ') ?: 'Already in sync.';
                        Notification::make()->title('DNS sync complete')->body($body)->success()->send();
                    } catch (Throwable $e) {
                        Notification::make()->title('DNS sync failed')->body($e->getMessage())->danger()->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
