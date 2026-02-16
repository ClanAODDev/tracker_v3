<?php

namespace App\Filament\Mod\Resources\MemberRequestResource\Pages;

use App\Filament\Mod\Resources\MemberRequestResource;
use App\Jobs\AddClanMember;
use App\Models\MemberRequest;
use App\Notifications\Channel\NotifyDivisionMemberRequestApproved;
use App\Notifications\Channel\NotifyDivisionMemberRequestHoldLifted;
use App\Notifications\Channel\NotifyDivisionMemberRequestPutOnHold;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMemberRequest extends EditRecord
{
    protected static string $resource = MemberRequestResource::class;

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->hidden();
    }

    protected function getHeaderActions(): array
    {
        /** @var MemberRequest $record */
        $record = $this->getRecord();

        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->visible(function (): bool {
                    $rec        = $this->getRecord();
                    $onHold     = $rec->newQuery()->onHold()->whereKey($rec)->exists();
                    $isApproved = (bool) ($rec->approved_at && is_null($rec->processed_at));

                    return ! $onHold && ! $isApproved;
                })
                ->requiresConfirmation()
                ->schema([
                    TextInput::make('member_name')
                        ->label('Member Name')
                        ->helperText('This name will be synced to the forum as AOD_{name}.')
                        ->required()
                        ->default(fn () => $this->getRecord()->member->name),
                ])
                ->action(function (array $data): void {
                    $rec  = $this->getRecord();
                    $user = auth()->user();

                    if ($rec->member->name !== $data['member_name']) {
                        $rec->member->update(['name' => $data['member_name']]);
                    }

                    $rec->division->notify(new NotifyDivisionMemberRequestApproved(
                        $user,
                        $rec->member
                    ));

                    $rec->approve();

                    AddClanMember::dispatch(member: $rec->member, admin_id: $user->member->clan_id);

                    $indexUrl = static::getResource()::getUrl('index') . '?filters[status][value]=approved';
                    $this->redirect($indexUrl, navigate: true);
                }),

            Action::make('placeOnHold')
                ->label('Place on Hold')
                ->color('warning')
                ->icon('heroicon-o-pause-circle')
                ->visible(function (): bool {
                    $rec        = $this->getRecord();
                    $onHold     = $rec->newQuery()->onHold()->whereKey($rec)->exists();
                    $isApproved = ($rec->approved_at && is_null($rec->processed_at));

                    return ! $onHold && ! $isApproved;
                })
                ->schema([
                    Textarea::make('notes')
                        ->label('Reason for placing on hold')
                        ->helperText('Division officers will be notified of the hold status.')
                        ->required()
                        ->rows(5),
                ])
                ->action(function (array $data): void {
                    $rec  = $this->getRecord();
                    $user = auth()->user();

                    $rec->placeOnHold($data['notes']);

                    $rec->division->notify(new NotifyDivisionMemberRequestPutOnHold(
                        $rec,
                        $user,
                        $rec->member
                    ));

                    $indexUrl = static::getResource()::getUrl('index') . '?filters[status][value]=on_hold';
                    $this->redirect($indexUrl, navigate: true);
                }),

            Action::make('removeHold')
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

                    $rec->division->notify(new NotifyDivisionMemberRequestHoldLifted(
                        $rec,
                        $rec->member
                    ));

                    $this->redirect(static::getResource()::getUrl('index'), navigate: true);
                }),

            DeleteAction::make()->visible(fn () => ! $record->isApproved()),

            Action::make('reprocess')
                ->label('Re-Process')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(function (): bool {
                    return $this->getRecord()->isApproved();
                })
                ->requiresConfirmation()
                ->action(function (): void {
                    $rec  = $this->getRecord();
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

                    $indexUrl = static::getResource()::getUrl('index') . '?filters[status][value]=approved';
                    $this->redirect($indexUrl, navigate: true);
                }),

        ];
    }
}
