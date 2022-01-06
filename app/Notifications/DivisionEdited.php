<?php

namespace App\Notifications;

use App\Channels\Messages\DiscordMessage;
use App\Channels\WebhookChannel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DivisionEdited extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [WebhookChannel::class];
    }

    /**
     * @param $notifiable
     *
     * @throws Exception
     *
     * @return array
     */
    public function toWebhook($notifiable)
    {
        $channel = $notifiable->settings()->get('slack_channel');

        $authoringUser = auth()->check() ? auth()->user()->name : 'ClanAOD';

        return (new DiscordMessage())
            ->to($channel)
            ->message(":tools: **{$authoringUser}** updated division settings for **{$notifiable->name}**")
            ->success()
            ->send();
    }
}
