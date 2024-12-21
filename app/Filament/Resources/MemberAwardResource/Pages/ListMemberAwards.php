<?php

namespace App\Filament\Resources\MemberAwardResource\Pages;

use App\Filament\Resources\MemberAwardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMemberAwards extends ListRecords
{
    protected static string $resource = MemberAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
