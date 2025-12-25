<?php

namespace App\Filament\Mod\Resources\DivisionTagResource\Pages;

use App\Filament\Mod\Resources\DivisionTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDivisionTag extends EditRecord
{
    protected static string $resource = DivisionTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
