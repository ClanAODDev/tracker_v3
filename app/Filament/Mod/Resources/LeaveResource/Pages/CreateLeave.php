<?php

namespace App\Filament\Mod\Resources\LeaveResource\Pages;

use App\Filament\Mod\Resources\LeaveResource;
use App\Models\Member;
use App\Models\Note;
use Filament\Resources\Pages\CreateRecord;

class CreateLeave extends CreateRecord
{
    protected static string $resource = LeaveResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $note = Note::create([
            'body' => 'Leave of absence requested. Reason: ' . $data['note']['body'],
            'member_id' => Member::whereClanId($data['member_id'])->first()->id,
            'author_id' => auth()->id(),
            'type' => 'misc'
        ]);

        $data['requester_id'] = auth()->id();

        $data['note_id'] = $note->id;

        unset($data['note']);

        return $data;

    }
}
