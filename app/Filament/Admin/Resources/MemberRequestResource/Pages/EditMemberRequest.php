<?php

namespace App\Filament\Admin\Resources\MemberRequestResource\Pages;

use App\Filament\Admin\Resources\MemberRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberRequest extends EditRecord
{
    protected static string $resource = MemberRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalDescription("Deleting a request that hasn't been approved withdraws the recruitment: the member is moved out of the division, and a Discord applicant goes back to the pending list."),
        ];
    }
}
