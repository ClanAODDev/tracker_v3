<?php

namespace App\Filament\Admin\Resources\AwardResource\Pages;

use App\Filament\Admin\Resources\AwardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\HtmlString;

class EditAward extends EditRecord
{
    protected static string $resource = AwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->modalDescription(new HtmlString(
                '<strong">This is a permanent and irreversible action</strong>. All member awards will be removed.'
            )),
        ];
    }
}
