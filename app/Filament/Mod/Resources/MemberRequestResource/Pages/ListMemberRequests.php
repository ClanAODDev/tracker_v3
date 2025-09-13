<?php

namespace App\Filament\Mod\Resources\MemberRequestResource\Pages;

use App\Filament\Mod\Resources\MemberRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListMemberRequests extends ListRecords
{
    protected static string $resource = MemberRequestResource::class;

    public function getTitle(): string
    {
        return 'Member Requests';
    }
}
