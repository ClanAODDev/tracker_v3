<?php

namespace App\Filament\Admin\Resources\SquadResource\Pages;

use App\Filament\Admin\Resources\SquadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSquad extends EditRecord
{
    protected static string $resource = SquadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
