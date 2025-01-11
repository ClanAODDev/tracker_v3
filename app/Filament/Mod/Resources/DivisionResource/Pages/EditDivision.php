<?php

namespace App\Filament\Mod\Resources\DivisionResource\Pages;

use App\Filament\Mod\Resources\DivisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mansoor\FilamentVersionable\Page\RevisionsAction;

class EditDivision extends EditRecord
{
    protected static string $resource = DivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            RevisionsAction::make(),
        ];
    }
}
