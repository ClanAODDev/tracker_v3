<?php

namespace App\Filament\Admin\Resources\FallenMemberResource\Pages;

use App\Filament\Admin\Resources\FallenMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFallenMembers extends ListRecords
{
    protected static string $resource = FallenMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
