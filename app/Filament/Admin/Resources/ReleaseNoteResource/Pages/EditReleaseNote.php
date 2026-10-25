<?php

namespace App\Filament\Admin\Resources\ReleaseNoteResource\Pages;

use App\Filament\Admin\Resources\ReleaseNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReleaseNote extends EditRecord
{
    protected static string $resource = ReleaseNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
