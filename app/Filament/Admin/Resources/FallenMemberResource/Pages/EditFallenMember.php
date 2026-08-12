<?php

namespace App\Filament\Admin\Resources\FallenMemberResource\Pages;

use App\Filament\Admin\Resources\FallenMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFallenMember extends EditRecord
{
    protected static string $resource = FallenMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
