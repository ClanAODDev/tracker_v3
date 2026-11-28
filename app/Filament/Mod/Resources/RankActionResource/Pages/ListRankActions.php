<?php

namespace App\Filament\Mod\Resources\RankActionResource\Pages;

use App\Enums\Ability;
use App\Filament\Mod\Resources\RankActionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRankActions extends ListRecords
{
    protected static string $resource = RankActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importHistory')
                ->label('Import History')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->url(RankActionResource::getUrl('import-history'))
                ->visible(fn () => auth()->user()?->can(Ability::ImportHistory)),
            CreateAction::make(),
        ];
    }
}
