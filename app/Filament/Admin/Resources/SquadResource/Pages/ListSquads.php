<?php

namespace App\Filament\Admin\Resources\SquadResource\Pages;

use App\Filament\Admin\Resources\SquadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSquads extends ListRecords
{
    protected static string $resource = SquadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
