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

    private $names;

    private $division;

    /**
     * Create a new notification instance.
     *
     * @param $names
     * @param $division
     */
    public function __construct($names, $division)
    {
        $this->names = $names;
        $this->division = $division;
    }

    public function via($notifiable)
    {
        return [WebhookChannel::class];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function toWebhook()
    {
        $channel = $this->division->settings()->get('slack_channel');

        return (new DiscordMessage())
            ->to($channel)
            ->message(addslashes(":tools: \"**MEMBER STATUS - NAME CHANGE**\n`{$this->names['oldName']}` is now known as `{$this->names['newName']}`. Please inform the member of this change."))
            ->success()
            ->send();
    }
}
