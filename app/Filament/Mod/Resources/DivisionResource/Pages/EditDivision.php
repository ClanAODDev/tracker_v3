<?php

namespace App\Filament\Mod\Resources\DivisionResource\Pages;

use App\Filament\Mod\Resources\DivisionResource;
use App\Notifications\DivisionEdited;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Mansoor\FilamentVersionable\Page\RevisionsAction;

class EditDivision extends EditRecord
{
    protected static string $resource = DivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            RevisionsAction::make(),
        ];
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        if ($record->settings()->get('voice_alert_division_edited')) {
            $record->notify(new DivisionEdited(auth()->user()->name));
        }

        return $record;
    }
}
