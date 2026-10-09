<?php

namespace App\Filament\Mod\Resources\UnitResource\Pages;

use App\Enums\Position;
use App\Filament\Mod\Resources\UnitResource;
use App\Models\Member;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
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

    private function parentOptions(): array
    {
        return Unit::query()
            ->where('division_id', $this->record->division_id)
            ->where('depth', $this->record->depth - 1)
            ->with('parent')
            ->orderBy('path')
            ->get()
            ->mapWithKeys(fn (Unit $unit) => [$unit->id => $unit->parent ? "{$unit->parent->name} / {$unit->name}" : $unit->name])
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('move')
                ->label('Move')
                ->icon('heroicon-o-arrows-right-left')
                ->color('gray')
                ->visible(fn () => $this->record->depth > 1 && auth()->user()->can('move', $this->record))
                ->modalHeading(fn () => sprintf('Move %s', $this->record->name))
                ->modalDescription(fn () => sprintf('Moves this %s and everything in it under another %s.', $this->record->levelLabel(), $this->record->division->unitLevel($this->record->depth - 1)?->label ?? 'Unit'))
                ->schema([
                    Select::make('parent_id')
                        ->label(fn () => $this->record->division->unitLevel($this->record->depth - 1)?->label ?? 'Parent')
                        ->options(fn () => $this->parentOptions())
                        ->default(fn () => $this->record->parent_id)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $parent = Unit::findOrFail($data['parent_id']);

                    app(UnitAssignment::class)->move($this->record, $parent);

                    Notification::make()
                        ->success()
                        ->title(sprintf('%s moved to %s', $this->record->name, $parent->name))
                        ->send();

                    return redirect(UnitResource::getUrl('edit', ['record' => $this->record]));
                }),

            DeleteAction::make()
                ->modalDescription(fn () => $this->record->isSquad()
                    ? sprintf('Assigned members will be moved up to the parent %s. Are you sure?', $this->record->parent?->levelLabel() ?? 'Unit')
                    : sprintf('Assigned members will be removed from this %s and every unit within it. Are you sure?', $this->record->levelLabel()))
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
