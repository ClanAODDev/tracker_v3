<?php

namespace App\Notifications;

use App\Channels\BotChannel;
use App\Channels\Messages\BotMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ITTeamAlert extends Notification implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    private mixed $title;
    private mixed $message;

    public function __construct($title, $message)
    {
        $this->title = $title;
        $this->message = $message;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [BotChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toBot($notifiable)
    {
        (new BotMessage())
            ->title($this->title)
            ->message($this->message)
            ->info()
            ->send();
    }
}
