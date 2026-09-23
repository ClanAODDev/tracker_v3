<?php

namespace App\Filament\Admin\Resources\DivisionMemberFieldResource\Pages;

use App\Filament\Admin\Resources\DivisionMemberFieldResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDivisionMemberField extends EditRecord
{
    protected static string $resource = DivisionMemberFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
