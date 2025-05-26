<?php

namespace App\Filament\Mod\Resources\MemberResource\Pages;

use App\Enums\Rank;
use App\Filament\Mod\Resources\MemberResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    public function getTitle(): string
    {
        return $this->record->division_id
            ? $this->record->name
            : sprintf('[Ex-AOD] %s', $this->record->name);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($record->isDirty('last_trained_by')) {
            $data['last_trained_at'] = now();
        }

        $record->update($data);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            //            Actions\DeleteAction::make(),
            Action::make('remove_from_forums')
                ->label('Remove From Forums')
                ->color('danger')
                ->icon('heroicon-o-link')
                ->extraAttributes([
                    'onclick' => "
                    if (!confirm(
                        'Are you sure you want to remove this member from the forums?'
                    )) {
                        event.preventDefault();
                    }
                ",
                ])
                ->url(fn (): string => sprintf(
                    'https://www.clanaod.net/forums/modcp/aodmember.php?do=remaod&u=%s',
                    $this->record->id,
                ))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->id !== auth()->user()->member->id
                    && auth()->user()->member->rank->value >= Rank::SERGEANT->value
                ),
        ];
    }
}
