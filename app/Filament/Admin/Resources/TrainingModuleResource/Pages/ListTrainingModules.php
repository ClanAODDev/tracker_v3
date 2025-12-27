<?php

namespace App\Filament\Admin\Resources\TrainingModuleResource\Pages;

use App\Filament\Admin\Resources\TrainingModuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingModules extends ListRecords
{
    protected static string $resource = TrainingModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
