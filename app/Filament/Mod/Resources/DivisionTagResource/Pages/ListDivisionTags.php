<?php

namespace App\Filament\Mod\Resources\DivisionTagResource\Pages;

use App\Filament\Mod\Resources\DivisionTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDivisionTags extends ListRecords
{
    protected static string $resource = DivisionTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
