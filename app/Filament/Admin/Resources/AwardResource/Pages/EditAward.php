<?php

namespace App\Filament\Admin\Resources\AwardResource\Pages;

use App\Filament\Admin\Resources\AwardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAward extends EditRecord
{
    protected static string $resource = AwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
