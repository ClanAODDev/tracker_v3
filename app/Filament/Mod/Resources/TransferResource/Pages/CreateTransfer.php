<?php

namespace App\Filament\Mod\Resources\TransferResource\Pages;

use App\Filament\Mod\Resources\TransferResource;
use App\Notifications\Channel\NotifyDivisionMemberTransferRequested;
use Filament\Resources\Pages\CreateRecord;

class CreateTransfer extends CreateRecord
{
    protected static string $resource = TransferResource::class;

    protected function afterCreate()
    {
        $record = $this->record;

        // notify current division
        $this->sendTransferNotification($this->record->member->division, $record->member, $record->division->name);

        // notify new division
        $this->sendTransferNotification($this->record->division, $record->member, $record->division->name);
    }

    private function sendTransferNotification($division, $member, $divisionName): void
    {
        $division->notify(new NotifyDivisionMemberTransferRequested($member, $divisionName));
    }
}
