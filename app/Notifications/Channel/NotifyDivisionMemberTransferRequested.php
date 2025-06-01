<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Models\Member;
use App\Traits\DivisionSettableNotification;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionMemberTransferRequested extends Notification implements ShouldQueue
{
    use DivisionSettableNotification, Queueable, RetryableNotification;

    private string $alertSetting = 'chat_alerts.member_transferred';

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly Member $member,
        private readonly string $destinationDivision,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via()
    {
        return [BotChannel::class];
    }

    /**
     * @return array[]
     *
     * @throws \Exception
     */
    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->target($notifiable->settings()->get($this->alertSetting))
            ->thumbnail($notifiable->getLogoPath())
            ->fields([
                [
                    'name' => '**MEMBER TRANSFER REQUEST**',
                    'value' => addslashes(sprintf(
                        ':recycle: A transfer request for %s [%s] to %s has been created',
                        $this->member->name,
                        $this->member->clan_id,
                        $this->destinationDivision
                    )),
                ], [
                    'name' => 'Manage Transfer Requests',
                    'value' => sprintf(
                        '[Click here to manage transfer requests](%s)',
                        route('filament.mod.resources.transfers.index')
                    ),
                ],
            ])->info()
            ->send();
    }
}
