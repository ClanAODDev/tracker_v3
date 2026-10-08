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
        return sprintf('Edit %s', $this->record->levelLabel());
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
                ->modalDescription(fn () => sprintf('Assigned members will be removed from this %s and every unit within it. Are you sure?', strtolower($this->record->levelLabel())))
                ->action(function ($record) {
                    $units = app(UnitAssignment::class);
                    $unit  = $record;

                    $unit->allMembers()->update(['unit_id' => null]);

                    Unit::query()
                        ->where('path', 'like', $unit->path . '%')
                        ->where('id', '<>', $unit->id)
                        ->orderByDesc('depth')
                        ->get()
                        ->each(fn (Unit $descendant) => $units->archive($descendant, recordActivity: false));

                    $units->archive($unit);

                    Notification::make()
                        ->success()
                        ->title(sprintf('%s has been deleted', $unit->levelLabel()))
                        ->body('Assigned members and units have been updated.')
                        ->send();

                    return $record->parent
                        ? redirect()->route('filament.mod.resources.platoons.edit', $record->parent)
                        : redirect()->route('filament.mod.resources.divisions.edit', $record->division);
                }),
        ];
    }
}
