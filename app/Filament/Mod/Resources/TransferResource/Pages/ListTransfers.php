<?php

namespace App\Filament\Mod\Resources\TransferResource\Pages;

use App\Enums\Ability;
use App\Filament\Mod\Resources\TransferResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransfers extends ListRecords
{
    protected static string $resource = TransferResource::class;

    protected static ?string $title = 'Division Transfers';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importHistory')
                ->label('Import History')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->url(TransferResource::getUrl('import-history'))
                ->visible(fn () => auth()->user()?->can(Ability::ImportHistory)),
            CreateAction::make(),
        ];
    }
}
