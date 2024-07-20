<?php

namespace App\Filament\Resources\PlatoonResource\Pages;

use App\Filament\Resources\PlatoonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlatoons extends ListRecords
{
    protected static string $resource = PlatoonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
