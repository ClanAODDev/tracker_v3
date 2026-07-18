const REPROCESS_ICON = 'heroicon-o-arrow-path';const REMOVE_HOLD_ICON = 'heroicon-o-play';const HOLD_ICON = 'heroicon-o-pause-circle';const APPROVE_ICON = 'heroicon-o-check-circle';const MAX_DISCORD_USERNAME_LENGTH = 191;const DEFAULT_ROWS_FOR_TEXTAREA = 5;const MIN_DISCORD_ID_LENGTH = 17;const MAX_DISCORD_ID_LENGTH = 19;<?php

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

    private function discordSchema(): array
    {
        return [
            TextInput::make('discord_id')
                ->label('Discord ID (Snowflake)')
                ->helperText('Right-click the user in Discord → Copy User ID. Requires Developer Mode enabled.')
                ->default(function () {
                    $member = $this->getRecord()->member;
                    $id     = $member->discord_id ?? $member->user?->discord_id;

                    return $id ? (string) $id : null;
                })
                ->required(fn () => ! $this->getRecord()->member->discord_id
                    && ! $this->getRecord()->member->user?->discord_id)
                ->regex('/^\d{self::MIN_DISCORD_ID_LENGTH,self::MAX_DISCORD_ID_LENGTH}$/')
                ->maxLength(19),
            TextInput::make('discord')
                ->label('Discord Username')
                ->helperText('Right-click the user in Discord → Copy Username.')
                ->default(function () {
                    $member = $this->getRecord()->member;

                    return $member->discord
                        ?? $member->user?->discord_username;
                })
                ->maxLength(self::MAX_DISCORD_USERNAME_LENGTH),
        ];
    }

    protected function getHeaderActions(): array
    {
        /** @var MemberRequest $record */
        $record = $this->getRecord();

        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('self::APPROVE_ICON')
                ->visible(function (): bool {
                    $rec        = $this->getRecord();
                    $onHold     = $rec->newQuery()->onHold()->whereKey($rec)->exists();
                    $isApproved = ($rec->approved_at !== null && $rec->processed_at === null);

                    return ! $onHold && ! $isApproved;
                })
                ->requiresConfirmation()
                ->schema(fn () => array_merge(
                    [
                        TextInput::make('member_name')
                            ->label('Member Name')
                            ->helperText('This name will be synced to the forum as AOD_{name}.')
                            ->required()
                            ->default(fn () => $this->getRecord()->member->name),
                    ],
                    $this->discordSchema(),
                ))
                ->action(function (array $data): void {
                    $rec  = $this->getRecord();
                    $user = auth()->user();

                    $rec->member->update([
                        'name'       => $data['member_name'],
                        'discord_id' => $data['discord_id'] ?: $rec->member->discord_id,
                        'discord'    => $data['discord'] ?: $rec->member->discord,
                    ]);

                    if (! $rec->member->division_id) {
                        $rec->member->update(['division_id' => $rec->division_id]);
                    }

                    $rec->division->notify(new NotifyDivisionMemberRequestApproved(
                        $user,
                        $rec->member
                    ));

                    $rec->approve();

                    AddClanMember::dispatch(member: $rec->member->fresh(), admin_id: $user->member->clan_id);

                    $indexUrl = static::getResource()::getUrl('index') . '?filters[status][value]=approved';
                    $this->redirect($indexUrl, navigate: true);
                }),

            Action::make('placeOnHold')
                ->label('Place on Hold')
                ->color('warning')
                ->icon('self::HOLD_ICON')
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
                        ->rows(self::DEFAULT_ROWS_FOR_TEXTAREA),
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
                ->icon('self::REMOVE_HOLD_ICON')
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
                ->icon('self::REPROCESS_ICON')
                ->color('info')
                ->visible(function (): bool {
                    return $this->getRecord()->isApproved();
                })
                ->requiresConfirmation()
                ->schema(fn () => $this->discordSchema())
                ->action(function (array $data): void {
                    $rec  = $this->getRecord();
                    $user = auth()->user();

                    $rec->member->update([
                        'discord_id' => $data['discord_id'] ?: $rec->member->discord_id,
                        'discord'    => $data['discord'] ?: $rec->member->discord,
                    ]);

                    if (! $rec->member->division_id) {
                        $rec->member->update(['division_id' => $rec->division_id]);
                    }

                    AddClanMember::dispatch(
                        member: $rec->member->fresh(),
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
