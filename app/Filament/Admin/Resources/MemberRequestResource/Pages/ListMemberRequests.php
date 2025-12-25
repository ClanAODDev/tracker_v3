<?php

namespace App\Filament\Admin\Resources\MemberRequestResource\Pages;

use App\Filament\Admin\Resources\MemberRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberRequests extends ListRecords
{
    protected static string $resource = MemberRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
