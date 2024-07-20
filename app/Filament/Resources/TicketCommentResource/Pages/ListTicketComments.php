<?php

namespace App\Filament\Resources\TicketCommentResource\Pages;

use App\Filament\Resources\TicketCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketComments extends ListRecords
{
    protected static string $resource = TicketCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
