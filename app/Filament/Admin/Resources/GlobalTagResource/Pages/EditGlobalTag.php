<?php

namespace App\Filament\Admin\Resources\GlobalTagResource\Pages;

use App\Filament\Admin\Resources\GlobalTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGlobalTag extends EditRecord
{
    protected static string $resource = GlobalTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
