<?php

namespace App\Filament\Mod\Resources\DivisionTagResource\Pages;

use App\Filament\Mod\Resources\DivisionTagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisionTags extends ListRecords
{
    protected static string $resource = DivisionTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
