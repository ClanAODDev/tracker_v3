<?php

namespace App\Filament\Admin\Resources\HandleResource\Pages;

use App\Filament\Admin\Resources\HandleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHandle extends EditRecord
{
    protected static string $resource = HandleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
