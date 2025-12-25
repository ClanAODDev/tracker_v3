<?php

namespace App\Filament\Mod\Resources\TransferResource\Pages;

use App\Filament\Mod\Resources\TransferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransfers extends ListRecords
{
    protected static string $resource = TransferResource::class;

    protected static ?string $title = 'Division Transfers';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
