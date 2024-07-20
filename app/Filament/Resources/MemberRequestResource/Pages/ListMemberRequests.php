<?php

namespace App\Filament\Resources\MemberRequestResource\Pages;

use App\Filament\Resources\MemberRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMemberRequests extends ListRecords
{
    protected static string $resource = MemberRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
