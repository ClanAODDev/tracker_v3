<?php

namespace App\Notifications;

use App\Channels\DiscordMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

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
     * @throws \Exception
     */
    public function toWebhook()
    {
        $channel = str_slug($this->division->name) . '-officers';

        return (new DiscordMessage())
            ->to($channel)
            ->message(":tools: \"**MEMBER STATUS - NAME CHANGE**\n`{$this->names['oldName']}` is now known as `{$this->names['newName']}`. Please inform the member of this change.")
            ->success()
            ->send();
    }
}
