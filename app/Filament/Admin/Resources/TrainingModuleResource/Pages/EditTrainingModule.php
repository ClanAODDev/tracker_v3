<?php

namespace App\Filament\Admin\Resources\TrainingModuleResource\Pages;

use App\Filament\Admin\Resources\TrainingModuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingModule extends EditRecord
{
    protected static string $resource = TrainingModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
