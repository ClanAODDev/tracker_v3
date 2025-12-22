<?php

namespace App\Filament\Mod\Resources\MemberResource\Pages;

use App\Filament\Mod\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_tags')
                ->label('Manage Tags')
                ->icon('heroicon-o-tag')
                ->url(MemberResource::getUrl('tags'))
                ->color('gray'),
            Actions\CreateAction::make(),
        ];
    }
}
