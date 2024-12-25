<?php

namespace App\Filament\Admin\Resources\CensusResource\Pages;

use App\Filament\Admin\Resources\CensusResource;
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
