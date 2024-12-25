<?php

namespace App\Filament\Admin\Resources\RankActionResource\Pages;

use App\Filament\Admin\Resources\RankActionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRankAction extends EditRecord
{
    protected static string $resource = RankActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
