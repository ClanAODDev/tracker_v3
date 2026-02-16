<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Notifications\Exception;
use App\Traits\RetryableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyAdminTicketCreated extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    public function via()
    {
        return [BotChannel::class];
    }

    /**
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        $fields = [
            [
                'name' => "Ticket: {$notifiable->type->name} - " . ($notifiable->caller
                        ? $notifiable->caller->name
                        : 'UNK'
                ),
                'value' => sprintf('[Open Ticket](%s)', route('help.tickets.show', $notifiable)),
            ],
        ];

        if ($notifiable->type->include_content_in_notification) {
            $fields[] = [
                'name'  => 'Description',
                'value' => str($notifiable->description)->limit(1024),
            ];
        }

        return new BotChannelMessage($notifiable)
            ->title('ClanAOD Tracker')
            ->target('help')
            ->fields($fields)
            ->info()
            ->send();
    }
}
