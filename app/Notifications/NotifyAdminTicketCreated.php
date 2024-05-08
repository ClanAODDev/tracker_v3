<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyAdminTicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

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
     * @param  mixed  $notifiable
     * @return array
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        $authoringUser = $notifiable->caller ? $notifiable->caller->name : 'UNK';

        return (new BotMessage())
            ->title('ClanAOD Tracker')
            ->messageId($notifiable->message_id)
            ->fields([
                [
                    'name' => "Type: {$notifiable->type->name}",
                    'value' => "Submitted by {$authoringUser}",
                ], [
                    'name' => 'Link to ticket',
                    'value' => route('help.tickets.show', $notifiable),
                ],
            ])
            ->info()
            ->send();
    }
}
