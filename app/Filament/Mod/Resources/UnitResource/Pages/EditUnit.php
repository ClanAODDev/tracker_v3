<?php

namespace App\Filament\Mod\Resources\UnitResource\Pages;

use App\Enums\Position;
use App\Filament\Mod\Resources\UnitResource;
use App\Models\Member;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditUnit extends EditRecord
{
    public function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return sprintf('Edit %s', $this->record->levelLabel());
    }

    protected static string $resource = UnitResource::class;

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

        $position = $unit->isSquad() ? Position::SQUAD_LEADER : Position::PLATOON_LEADER;

        $originalLeaderId = $state['original_leader_id'] ?? null;
        $newLeaderId      = (int) $this->record->leader_id;

        if ($originalLeaderId !== $newLeaderId) {
            if ($newLeaderId) {
                Member::where('clan_id', $newLeaderId)->update([
                    'unit_id'  => $unit->id,
                    'position' => $position,
                ]);

                $units->clearLeadership([$newLeaderId], except: $unit);
            }

            $originalLeaderStillLeader = $originalLeaderId && Member::where('clan_id', $originalLeaderId)
                ->where('position', $position)
                ->exists();

            if ($originalLeaderStillLeader) {
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
                ->modalDescription(fn () => $this->record->isSquad()
                    ? sprintf('Assigned members will be moved up to the parent %s. Are you sure?', strtolower($this->record->parent?->levelLabel() ?? 'unit'))
                    : sprintf('Assigned members will be removed from this %s and every unit within it. Are you sure?', strtolower($this->record->levelLabel())))
                ->action(function ($record) {
                    $units = app(UnitAssignment::class);
                    $unit  = $record;

                    if ($unit->isSquad()) {
                        $unit->members()->update(['unit_id' => $unit->parent_id]);
                    } else {
                        $unit->allMembers()->update(['unit_id' => null]);

                        Unit::query()
                            ->where('path', 'like', $unit->path . '%')
                            ->where('id', '<>', $unit->id)
                            ->orderByDesc('depth')
                            ->get()
                            ->each(fn (Unit $descendant) => $units->archive($descendant, recordActivity: false));
                    }

                    $units->archive($unit);

                    Notification::make()
                        ->success()
                        ->title(sprintf('%s has been deleted', $unit->levelLabel()))
                        ->body('Assigned members and units have been updated.')
                        ->send();

                    return $record->parent
                        ? redirect()->route('filament.mod.resources.units.edit', $record->parent)
                        : redirect()->route('filament.mod.resources.divisions.edit', $record->division);
                }),
        ];
    }
}
