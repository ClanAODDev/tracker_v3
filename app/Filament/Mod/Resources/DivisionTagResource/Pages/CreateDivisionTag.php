<?php

namespace App\Filament\Mod\Resources\DivisionTagResource\Pages;

use App\Filament\Mod\Resources\DivisionTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDivisionTag extends CreateRecord
{
    protected static string $resource = DivisionTagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['division_id'] = auth()->user()->member?->division_id;

        return $data;
    }
}
