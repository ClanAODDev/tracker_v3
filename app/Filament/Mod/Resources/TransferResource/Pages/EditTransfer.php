<?php

namespace App\Filament\Mod\Resources\TransferResource\Pages;

use App\Filament\Mod\Resources\TransferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransfer extends EditRecord
{
    protected static string $resource = TransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
