<?php

namespace App\Filament\Admin\Resources\GlobalTagResource\Pages;

use App\Filament\Admin\Resources\GlobalTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGlobalTags extends ListRecords
{
    protected static string $resource = GlobalTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
