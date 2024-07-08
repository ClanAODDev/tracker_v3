<?php

namespace App\Filament\Resources\MemberRequestResource\Pages;

use App\Filament\Resources\MemberRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMemberRequest extends EditRecord
{
    protected static string $resource = MemberRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
