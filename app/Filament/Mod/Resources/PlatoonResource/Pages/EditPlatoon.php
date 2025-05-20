<?php

namespace App\Filament\Mod\Resources\PlatoonResource\Pages;

use App\Enums\Position;
use App\Filament\Mod\Resources\PlatoonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlatoon extends EditRecord
{
    public function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return sprintf('Edit %s', $this->record->division->locality('Platoon'));
    }

    protected static string $resource = PlatoonResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $this->form->fill([
            ...$this->form->getState(),
            'original_leader_id' => $this->record->leader_id,
        ]);
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();

        $originalLeaderId = $state['original_leader_id'] ?? null;
        $newLeaderId = (int) $this->record->leader_id;

        if ($originalLeaderId !== $newLeaderId) {
            if ($newLeaderId) {
                \App\Models\Member::where('clan_id', $newLeaderId)?->update([
                    'position' => \App\Enums\Position::PLATOON_LEADER,
                    'platoon_id' => $this->record->id,
                    'squad_id' => 0,
                ]);
            }

            \App\Models\Member::where('clan_id', $originalLeaderId)?->update([
                'position' => Position::MEMBER,
            ]);
        }
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
