<?php

namespace App\Filament\Admin\Resources\MemberAwardResource\Pages;

use App\Filament\Admin\Resources\MemberAwardResource;
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
