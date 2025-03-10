<?php

namespace App\Filament\Mod\Resources\PlatoonResource\Pages;

use App\Filament\Mod\Resources\PlatoonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlatoon extends EditRecord
{
    protected static string $resource = PlatoonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
