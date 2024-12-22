<?php

namespace App\Filament\Mod\Resources\SquadResource\Pages;

use App\Filament\Mod\Resources\SquadResource;
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
