<?php

namespace App\Filament\Mod\Resources\DivisionResource\Pages;

use App\Filament\Mod\Resources\DivisionResource;
use App\Notifications\Channel\NotifyDivisionSettingsEdited;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDivision extends EditRecord
{
    protected static string $resource = DivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $protectedSettings = ['officer_channel', 'member_channel'];

        if (isset($data['settings'])) {
            $existingSettings = $record->getRawOriginal('settings');
            $existingSettings = is_string($existingSettings) ? json_decode($existingSettings, true) : ($existingSettings ?? []);

            foreach ($protectedSettings as $key) {
                if (isset($existingSettings[$key]) && ! isset($data['settings'][$key])) {
                    $data['settings'][$key] = $existingSettings[$key];
                }
            }
        }

        $record->update($data);
        $record->notify(new NotifyDivisionSettingsEdited(auth()->user()));

        return $record;
    }
}
