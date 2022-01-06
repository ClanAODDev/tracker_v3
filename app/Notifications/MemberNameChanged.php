<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberNameChanged extends Notification
{
    use Queueable;

    public function __construct(private $names)
    {
    }

    public function via($notifiable)
    {
        return [WebhookChannel::class];
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('slack_channel');

        return (new DiscordMessage())
            ->to($channel)
            ->message(addslashes(":tools: **MEMBER STATUS - NAME CHANGE**\n`{$this->names['oldName']}` is now known as `{$this->names['newName']}`. Please inform the member of this change."))
            ->success()
            ->send();
    }
}
