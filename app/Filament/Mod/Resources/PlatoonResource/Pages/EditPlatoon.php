<?php

namespace App\Filament\Mod\Resources\PlatoonResource\Pages;

use App\Enums\Position;
use App\Filament\Mod\Resources\PlatoonResource;
use App\Models\Member;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $units = app(UnitAssignment::class);
        $units->update($record, Arr::only($data, ['name', 'description', 'logo', 'order', 'leader_id']));

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
                    'position' => Position::PLATOON_LEADER,
                ]);

                $units->clearLeadership([$newLeaderId], except: $unit);
            }

            $originalLeaderStillPlatoonLeader = $originalLeaderId && Member::where('clan_id', $originalLeaderId)
                ->where('position', Position::PLATOON_LEADER)
                ->exists();

            if ($originalLeaderStillPlatoonLeader) {
                Member::where('clan_id', $originalLeaderId)->update([
                    'unit_id'  => null,
                    'position' => Position::MEMBER,
                ]);

                $units->clearLeadership([$originalLeaderId], except: $unit);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [

            DeleteAction::make()
                ->modalDescription('Assigned members will be removed from this platoon and any squads within. Are you sure?')
                ->action(function ($record) {
                    $units = app(UnitAssignment::class);
                    $unit  = $record;

                    $unit->allMembers()->update(['unit_id' => null]);

                    $unit->children()->get()->each(fn (Unit $squad) => $units->archive($squad, recordActivity: false));

                    $units->archive($unit);

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
