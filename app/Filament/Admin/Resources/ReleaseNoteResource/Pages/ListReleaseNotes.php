<?php

namespace App\Filament\Admin\Resources\ReleaseNoteResource\Pages;

use App\Filament\Admin\Resources\ReleaseNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReleaseNotes extends ListRecords
{
    protected static string $resource = ReleaseNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
