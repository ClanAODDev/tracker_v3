<?php

namespace App\Notifications\Channel;

use App\Channels\BotChannel;
use App\Channels\Messages\BotChannelMessage;
use App\Filament\Admin\Resources\TicketResource;
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
        return new BotChannelMessage($notifiable)
            ->title('ClanAOD Tracker')
            ->target('help')
            ->fields([
                [
                    'name' => "Ticket: {$notifiable->type->name} - " . ($notifiable->caller
                            ? $notifiable->caller->name
                            : 'UNK'
                        ),
                    'value' => sprintf('[Open Ticket](%s)', TicketResource::getUrl('edit', [
                        'record' => $notifiable
                    ])),
                ],
            ])
            ->info()
            ->send();
    }
}
