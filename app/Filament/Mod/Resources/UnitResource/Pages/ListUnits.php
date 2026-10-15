<?php

namespace App\Filament\Mod\Resources\UnitResource\Pages;

use App\Filament\Mod\Resources\UnitResource;
use Filament\Resources\Pages\ListRecords;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
