<?php

namespace App\Filament\Mod\Resources\PlatoonResource\Pages;

use App\Enums\Position;
use App\Filament\Mod\Resources\PlatoonResource;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
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
                Member::where('clan_id', $newLeaderId)->update([
                    'position' => Position::PLATOON_LEADER,
                    'platoon_id' => $this->record->id,
                    'squad_id' => 0,
                ]);

                Platoon::where('leader_id', $newLeaderId)->where('id', '!=',
                    $this->record->id)->update(['leader_id' => null]);

                Squad::where('leader_id', $newLeaderId)->where('id', '!=',
                    $this->record->id)->update(['leader_id' => null]);
            }

            if ($originalLeaderId) {
                Member::where('clan_id', $originalLeaderId)->update([
                    'position' => Position::MEMBER,
                    'platoon_id' => null,
                    'squad_id' => null,
                ]);

                Platoon::where('leader_id', $originalLeaderId)->where('id', '!=',
                    $this->record->id)->update(['leader_id' => null]);

                Squad::where('leader_id', $originalLeaderId)->update(['leader_id' => null]);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [

            DeleteAction::make()
                ->modalDescription('Assigned members will be removed from this platoon and any squads within. Are you sure?')
                ->action(function ($record) {
                    Member::where('platoon_id', $record->id)->update([
                        'platoon_id' => 0,
                        'squad_id' => 0,
                    ]);

                    Squad::where('platoon_id', $record->id)->delete();

                    $record->delete();

                    Notification::make()
                        ->success()
                        ->title('Platoon has been deleted')
                        ->body('Assigned members and squads have been updated.')
                        ->send();

                    return redirect()->route('filament.mod.resources.divisions.edit', $record->division);
                }),
        ];
    }
}
