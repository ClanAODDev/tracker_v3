<?php

namespace App\Filament\Mod\Resources\SquadResource\Pages;

use App\Enums\Position;
use App\Filament\Mod\Resources\SquadResource;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSquad extends EditRecord
{
    public function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return sprintf('Edit %s', $this->record->division->locality('Squad'));
    }

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
        $newLeaderId      = (int) $this->record->leader_id;

        if ($originalLeaderId !== $newLeaderId) {
            if ($newLeaderId) {
                Member::where('clan_id', $newLeaderId)->update([
                    'position'   => Position::SQUAD_LEADER,
                    'platoon_id' => $this->record->platoon_id,
                    'squad_id'   => $this->record->id,
                ]);

                Platoon::where('leader_id', $newLeaderId)->update(['leader_id' => null]);

                Squad::where('leader_id', $newLeaderId)->where('id', '!=', $this->record->id)
                    ->update(['leader_id' => null]);
            }

            if ($originalLeaderId) {
                Member::where('clan_id', $originalLeaderId)->update([
                    'position'   => Position::MEMBER,
                    'platoon_id' => null,
                    'squad_id'   => null,
                ]);

                Platoon::where('leader_id', $originalLeaderId)
                    ->where('id', '!=', $this->record->id)
                    ->update(['leader_id' => null]);

                Squad::where('leader_id', $originalLeaderId)
                    ->where('id', '!=', $this->record->id)
                    ->update(['leader_id' => null]);
            }
        }
    }

    protected static string $resource = SquadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalDescription('Assigned members will be removed from this squad. Are you sure?')
                ->action(function ($record) {
                    Member::where('squad_id', $record->id)->update([
                        'squad_id' => 0,
                    ]);

                    $record->delete();

                    Notification::make()
                        ->success()
                        ->title('Squad has been deleted')
                        ->body('Assigned members have been updated.')
                        ->send();

                    return redirect()->route('filament.mod.resources.platoons.edit', $record->platoon);
                }),
        ];
    }
}
