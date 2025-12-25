<?php

namespace App\Filament\Admin\Resources\TicketCommentResource\Pages;

use App\Filament\Admin\Resources\TicketCommentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketComments extends ListRecords
{
    protected static string $resource = TicketCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
