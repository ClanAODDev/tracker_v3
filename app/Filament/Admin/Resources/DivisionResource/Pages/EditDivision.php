<?php

namespace App\Filament\Admin\Resources\DivisionResource\Pages;

use App\Enums\Position;
use App\Filament\Admin\Resources\DivisionResource;
use App\Models\Member;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $divisionId = $this->record->id;

        if (isset($data['new_co'])) {
            $data = $this->handleNewCO($divisionId, $data);
        }

        $data = $this->handleXOs($divisionId, $data);

        unset($data['executive_officers'], $data['new_co']);

        return $data;
    }

    protected function handleNewCO(mixed $divisionId, array $data): array
    {
        $previousCoId = Member::where('division_id', $divisionId)
            ->where('position', Position::COMMANDING_OFFICER)
            ->value('id');

        $newCoId = $data['new_co'];

        if ($previousCoId && $previousCoId !== $newCoId) {
            Member::where('id', $previousCoId)
                ->update([
                    'position' => Position::MEMBER]);
            Member::where('id', $newCoId)
                ->update([
                    'position' => Position::COMMANDING_OFFICER,
                    'platoon_id' => 0,
                    'squad_id' => 0,
                ]);
        } elseif (! $previousCoId) {
            Member::where('id', $newCoId)
                ->update([
                    'position' => Position::COMMANDING_OFFICER,
                    'platoon_id' => 0,
                    'squad_id' => 0,
                ]);
        }

        return $data;
    }

    protected function handleXOs(mixed $divisionId, array $data): array
    {
        $previousXos = Member::where('division_id', $divisionId)
            ->where('position', Position::EXECUTIVE_OFFICER)
            ->pluck('id');

        $newXos = collect($data['executive_officers'] ?? [])->pluck('xo');

        $toRemove = $previousXos->diff($newXos);
        if ($toRemove->isNotEmpty()) {
            Member::whereIn('id', $toRemove)->update([
                'position' => Position::MEMBER]);
        }

        $toAdd = $newXos->diff($previousXos);
        if ($toAdd->isNotEmpty()) {
            Member::whereIn('id', $toAdd)
                ->update([
                    'position' => Position::EXECUTIVE_OFFICER,
                    'platoon_id' => 0,
                    'squad_id' => 0,
                ]);
        }

        return $data;
    }
}
