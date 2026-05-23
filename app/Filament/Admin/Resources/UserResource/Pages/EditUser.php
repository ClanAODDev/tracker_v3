<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Services\ForumProcedureService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncDiscordToMember')
                ->label('Sync Discord to Member')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => $this->record->member_id !== null)
                ->requiresConfirmation()
                ->modalDescription('This will overwrite the member\'s discord_id and discord username with the values from this user record.')
                ->action(function (): void {
                    $user   = $this->record;
                    $member = $user->member;

                    $member->update([
                        'discord_id' => $user->discord_id,
                        'discord'    => $user->discord_username,
                    ]);

                    if ($user->discord_id && $user->discord_username) {
                        app(ForumProcedureService::class)->setDiscordInfo(
                            userId: $member->clan_id,
                            discordId: $user->discord_id,
                            discordTag: $user->discord_username,
                        );
                    }

                    Notification::make()
                        ->title('Discord info synced to member and forum.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
            Action::make('Impersonate')
                ->label('Impersonate')
                ->authorize('impersonate')
                ->url(fn () => route('impersonate', ['user' => $this->record->id])),
        ];
    }
}
