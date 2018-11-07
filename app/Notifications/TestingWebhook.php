<?php

namespace App\Notifications;

use App\Channels\WebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestingWebhook extends Notification
{
    use Queueable;

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
        $json = json_encode([
            'username' => 'Blimey',
            'embed' => [
                'title' => 'This is a title... and here are some fields...',
                'author' => [
                    'name' => 'AOD Tracker'
                ],
                'color' => 10181046,
                'fields' => [
                    'name' => 'A name',
                    'value' => "A value"
                ]
            ]
        ]);

        $message = "This is a test";

//        $data = "\"general\" \"{$message}\" ";

        return [
            'content' => "!relay general '{$json}'",
        ];
    }
}
