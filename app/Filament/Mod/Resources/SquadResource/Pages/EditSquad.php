<?php

namespace App\Filament\Mod\Resources\SquadResource\Pages;

use App\Enums\Position;
use App\Filament\Mod\Resources\SquadResource;
use App\Models\Member;
use App\Services\Units\UnitAssignment;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $units = app(UnitAssignment::class);
        $units->update($record, Arr::only($data, ['name', 'logo', 'gen_pop', 'leader_id']));

        return $record->refresh();
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        $units = app(UnitAssignment::class);
        $unit  = $this->record;

        $originalLeaderId = $state['original_leader_id'] ?? null;
        $newLeaderId      = (int) $this->record->leader_id;

        if ($originalLeaderId !== $newLeaderId) {
            if ($newLeaderId) {
                Member::where('clan_id', $newLeaderId)->update([
                    'unit_id'  => $unit->id,
                    'position' => Position::SQUAD_LEADER,
                ]);

                $units->clearLeadership([$newLeaderId], except: $unit);
            }

            $originalLeaderStillSquadLeader = $originalLeaderId && Member::where('clan_id', $originalLeaderId)
                ->where('position', Position::SQUAD_LEADER)
                ->exists();

            if ($originalLeaderStillSquadLeader) {
                Member::where('clan_id', $originalLeaderId)->update([
                    'unit_id'  => null,
                    'position' => Position::MEMBER,
                ]);

                $units->clearLeadership([$originalLeaderId], except: $unit);
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
                    $units = app(UnitAssignment::class);
                    $unit  = $record;

                    $unit->members()->update(['unit_id' => $unit->parent_id]);

                    $units->archive($unit);

                    Notification::make()
                        ->success()
                        ->title('Squad has been deleted')
                        ->body('Assigned members have been updated.')
                        ->send();

                    return redirect()->route('filament.mod.resources.platoons.edit', $record->parent);
                }),
        ];
    }
}
