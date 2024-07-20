<?php

namespace App\Filament\Resources\CensusResource\Pages;

use App\Filament\Resources\CensusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCensus extends EditRecord
{
    protected static string $resource = CensusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
