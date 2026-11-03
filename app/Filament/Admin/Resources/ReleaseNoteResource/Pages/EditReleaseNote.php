<?php

namespace App\Filament\Admin\Resources\ReleaseNoteResource\Pages;

use App\Filament\Admin\Resources\ReleaseNoteResource;
use App\Models\ReleaseNote;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReleaseNote extends EditRecord
{
    protected static string $resource = ReleaseNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewPosting')
                ->label('View posting')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (ReleaseNote $record): string => route('changelog.show', $record->slug), shouldOpenInNewTab: true)
                ->visible(fn (ReleaseNote $record): bool => $record->status() === 'published'),
            DeleteAction::make(),
        ];
    }
}
