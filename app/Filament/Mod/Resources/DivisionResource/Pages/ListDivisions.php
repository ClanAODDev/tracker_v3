<?php

namespace App\Filament\Mod\Resources\DivisionResource\Pages;

use App\Filament\Mod\Resources\DivisionResource;
use Filament\Resources\Pages\ListRecords;

class ListDivisions extends ListRecords
{
    protected static string $resource = DivisionResource::class;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user?->isRole('admin')) {
            parent::mount();

            return;
        }

        $division = $user?->member?->division;

        if ($division) {
            $this->redirect(DivisionResource::getUrl('edit', ['record' => $division]));

            return;
        }

        abort(403, 'You do not have a division to manage.');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
