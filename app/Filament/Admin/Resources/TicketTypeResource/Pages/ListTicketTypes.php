<?php

namespace App\Filament\Admin\Resources\TicketTypeResource\Pages;

use App\Filament\Admin\Resources\TicketTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketTypes extends ListRecords
{
    protected static string $resource = TicketTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
