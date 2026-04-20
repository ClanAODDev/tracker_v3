<?php

namespace App\Notifications\React;

use App\Channels\BotChannel;
use App\Channels\Messages\BotReactMessage;
use App\Exceptions\MessageIdNotYetAvailableException;
use App\Traits\RetryableNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TicketReaction extends Notification implements ShouldQueue
{
    use Queueable, RetryableNotification;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private readonly string $status) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if (empty($notifiable->external_message_id)) {
            throw new MessageIdNotYetAvailableException;
        }

        Log::info('TicketReaction firing', [
            'ticket_id'           => $notifiable->id,
            'external_message_id' => $notifiable->external_message_id,
            'status'              => $this->status,
        ]);

        return [BotChannel::class];
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    public function toBot($notifiable)
    {
        return new BotReactMessage($notifiable)
            ->status($this->status)
            ->send();
    }
}
