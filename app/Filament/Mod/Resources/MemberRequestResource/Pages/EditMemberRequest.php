<?php

namespace App\Filament\Mod\Resources\MemberRequestResource\Pages;

use App\Filament\Mod\Resources\MemberRequestResource;
use App\Jobs\AddClanMember;
use App\Models\MemberRequest;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMemberRequest extends EditRecord
{
    protected static string $resource = MemberRequestResource::class;

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()->hidden();
    }

    protected function getHeaderActions(): array
    {
        /** @var MemberRequest $record */
        $record = $this->getRecord();

        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->visible(function (): bool {
                    $rec = $this->getRecord();
                    $onHold = $rec->newQuery()->onHold()->whereKey($rec)->exists();
                    $isApproved = (bool) ($rec->approved_at && is_null($rec->processed_at));

                    return ! $onHold && ! $isApproved;
                })
                ->requiresConfirmation()
                ->action(function (): void {
                    $rec = $this->getRecord();
                    $user = auth()->user();

                    $rec->division->notify(new \App\Notifications\Channel\NotifyDivisionMemberRequestApproved(
                        $user,
                        $rec->member
                    ));

                    $rec->approve();

                    AddClanMember::dispatch(member: $rec->member, admin_id: $user->member->clan_id);

                    $indexUrl = static::getResource()::getUrl('index') . '?tableFilters[status][value]=approved';
                    $this->redirect($indexUrl, navigate: true);
                }),

            Actions\Action::make('placeOnHold')
                ->label('Place on Hold')
                ->color('warning')
                ->icon('heroicon-o-pause-circle')
                ->visible(function (): bool {
                    $rec = $this->getRecord();
                    $onHold = $rec->newQuery()->onHold()->whereKey($rec)->exists();
                    $isApproved = ($rec->approved_at && is_null($rec->processed_at));

                    return ! $onHold && ! $isApproved;
                })
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Reason for placing on hold')
                        ->helperText('Division officers will be notified of the hold status.')
                        ->required()
                        ->rows(5),
                ])
                ->action(function (array $data): void {
                    $rec = $this->getRecord();
                    $user = auth()->user();

                    $rec->placeOnHold($data['notes']);

                    $rec->division->notify(new \App\Notifications\Channel\NotifyDivisionMemberRequestPutOnHold(
                        $rec,
                        $user,
                        $rec->member
                    ));

                    $indexUrl = static::getResource()::getUrl('index') . '?tableFilters[status][value]=on_hold';
                    $this->redirect($indexUrl, navigate: true);
                }),

            Actions\Action::make('removeHold')
                ->label('Remove Hold')
                ->icon('heroicon-o-play')
                ->visible(function (): bool {
                    $rec = $this->getRecord();

                    return $rec->newQuery()->onHold()->whereKey($rec)->exists();
                })
                ->requiresConfirmation()
                ->action(function (): void {
                    $rec = $this->getRecord();

                    $rec->removeHold();

                    $rec->division->notify(new \App\Notifications\Channel\NotifyDivisionMemberRequestHoldLifted(
                        $rec,
                        $rec->member
                    ));

                    $this->redirect(static::getResource()::getUrl('index'), navigate: true);
                }),

            Actions\DeleteAction::make()->visible(fn () => ! $record->isApproved()),

            Actions\Action::make('reprocess')
                ->label('Re-Process')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(function (): bool {
                    return $this->getRecord()->isApproved();
                })
                ->requiresConfirmation()
                ->action(function (): void {
                    $rec = $this->getRecord();
                    $user = auth()->user();

                    // Just (re)queue the job — no status changes here
                    AddClanMember::dispatch(
                        member: $rec->member,
                        admin_id: $user->member->clan_id
                    );

                    Notification::make()
                        ->title('Re-process job queued.')
                        ->success()
                        ->send();

                    $indexUrl = static::getResource()::getUrl('index') . '?tableFilters[status][value]=approved';
                    $this->redirect($indexUrl, navigate: true);
                }),

        ];
    }
}
