<?php

namespace App\Filament\Admin\Resources\MemberResource\Pages;

use App\Filament\Admin\Resources\MemberResource;
use App\Models\Division;
use App\Services\BulkTransferService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected ?int $pendingDivisionId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $originalDivisionId = (int) $this->record->getOriginal('division_id');

        if (isset($data['division_id']) && (int) $data['division_id'] !== $originalDivisionId) {
            $this->pendingDivisionId = (int) $data['division_id'];
            unset($data['division_id']);

            // Undo the in-memory association Select::relationship() already applied to
            // $this->record, so the record stays at its original division until the
            // BulkTransferService transfer below is what actually moves it.
            $this->record->division_id = $originalDivisionId;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->pendingDivisionId === null) {
            return;
        }

        $targetDivision = Division::findOrFail($this->pendingDivisionId);

        app(BulkTransferService::class)->transfer(collect([$this->record]), $targetDivision);

        $this->pendingDivisionId = null;
    }
}
