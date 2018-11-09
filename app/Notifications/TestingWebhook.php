<?php

namespace App\Notifications;

use App\Channels\DiscordMessage;
use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestingWebhook extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param $division
     */
    public function __construct($division)
    {
        $this->division = $division;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [WebhookChannel::class];
    }

    public function toWebhook($notifiable)
    {
        $channel = str_slug($this->division->name) . '-officers';

        $authoringUser = auth()->check() ? auth()->user()->name : 'ClanAOD';

        return (new DiscordMessage())
            ->to($channel)
            ->message("{$authoringUser} updated division settings for {$this->division->name}")
            ->success()
            ->send();
    }
}
