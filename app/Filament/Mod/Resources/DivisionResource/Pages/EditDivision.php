<?php

namespace App\Filament\Mod\Resources\DivisionResource\Pages;

use App\Filament\Mod\Resources\DivisionResource;
use App\Notifications\Channel\NotifyDivisionSettingsEdited;
use App\Notifications\Channel\TestChannelNotification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDivision extends EditRecord
{
    protected static string $resource = DivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('test_officers')
                    ->label('Test Officers Channel')
                    ->icon('heroicon-o-megaphone')
                    ->color('warning')
                    ->action(function () {
                        $record = $this->getRecord();
                        $channel = $record->routeNotificationForOfficers();
                        if (! $channel) {
                            Notification::make()
                                ->title('No officers channel configured')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->notify(new TestChannelNotification('officers'));
                        Notification::make()
                            ->title('Test sent to ' . $channel)
                            ->success()
                            ->send();
                    }),
                Action::make('test_members')
                    ->label('Test Members Channel')
                    ->icon('heroicon-o-megaphone')
                    ->color('info')
                    ->action(function () {
                        $record = $this->getRecord();
                        $channel = $record->routeNotificationForMembers();
                        if (! $channel) {
                            Notification::make()
                                ->title('No members channel configured')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->notify(new TestChannelNotification('members'));
                        Notification::make()
                            ->title('Test sent to ' . $channel)
                            ->success()
                            ->send();
                    }),
            ])->label('Test Channels')->icon('heroicon-o-bell')->button(),
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $protectedSettings = ['officer_channel', 'member_channel'];

        if (isset($data['settings'])) {
            $existingSettings = $record->getRawOriginal('settings');
            $existingSettings = is_string($existingSettings) ? json_decode($existingSettings, true) : ($existingSettings ?? []);

            foreach ($protectedSettings as $key) {
                if (isset($existingSettings[$key]) && ! isset($data['settings'][$key])) {
                    $data['settings'][$key] = $existingSettings[$key];
                }
            }
        }

        $record->update($data);
        $record->notify(new NotifyDivisionSettingsEdited(auth()->user()));

        return $record;
    }
}
