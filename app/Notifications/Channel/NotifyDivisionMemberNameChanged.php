<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyDivisionMemberNameChanged extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function __construct(private readonly array $names) {}

    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        return (new BotChannelMessage($notifiable))
            ->title($notifiable->name . ' Division')
            ->thumbnail($notifiable->getLogoPath())
            ->message(addslashes(sprintf(
                ":tools: **MEMBER STATUS - NAME CHANGE**\n`%s` is now known as `%s`. Please inform the member of this change.",
                $this->names['oldName'],
                $this->names['newName']
            )))
            ->success()
            ->send();
    }
}
