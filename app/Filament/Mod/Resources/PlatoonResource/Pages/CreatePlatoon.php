<?php

namespace App\Filament\Mod\Resources\PlatoonResource\Pages;

use App\Filament\Mod\Resources\PlatoonResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePlatoon extends CreateRecord
{
    protected static string $resource = PlatoonResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['division_id'] = auth()->user()->member->division_id;

        return $data;
    }
}
