<?php

namespace App\Filament\Admin\Resources\MemberAwardResource\Pages;

use App\Filament\Admin\Resources\MemberAwardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberAward extends EditRecord
{
    protected static string $resource = MemberAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
