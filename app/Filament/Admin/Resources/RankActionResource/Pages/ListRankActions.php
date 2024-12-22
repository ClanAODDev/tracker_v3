<?php

namespace App\Filament\Admin\Resources\RankActionResource\Pages;

use App\Filament\Admin\Resources\RankActionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRankActions extends ListRecords
{
    protected static string $resource = RankActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
